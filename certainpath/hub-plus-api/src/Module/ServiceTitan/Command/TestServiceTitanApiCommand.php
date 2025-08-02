<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Command;

use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Exception\ServiceTitanApiException;
use App\Module\ServiceTitan\Service\ServiceTitanAuthService;
use App\Module\ServiceTitan\Service\ServiceTitanCredentialEncryptionService;
use App\Module\ServiceTitan\ValueObject\OAuthResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'servicetitan:api:test',
    description: 'Test ServiceTitan API connectivity and basic operations',
)]
class TestServiceTitanApiCommand extends Command
{
    // Available test endpoints
    private const array TEST_ENDPOINTS = [
        'companies' => '/api/v2/tenant/{tenant}/companies',
        'employees' => '/settings/v2/tenant/{tenant}/employees',  // Note: different base path
        'customers' => '/api/v2/tenant/{tenant}/crm/customers',
        'invoices' => '/api/v2/tenant/{tenant}/accounting/invoices',
        'jobs' => '/api/v2/tenant/{tenant}/jpm/jobs',
        'technicians' => '/api/v2/tenant/{tenant}/dispatch/technicians',
        'business-units' => '/settings/v2/tenant/{tenant}/business-units',  // Note: different base path
        'locations' => '/api/v2/tenant/{tenant}/crm/locations',
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ServiceTitanAuthService $authService,
        private readonly ServiceTitanCredentialEncryptionService $encryptionService,
        private readonly LoggerInterface $logger,
        private readonly string $serviceTitanClientId,
        private readonly string $serviceTitanClientSecret,
        private readonly string $serviceTitanAppKey,
        private readonly string $serviceTitanEncryptionKey,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('tenant-id', null, InputOption::VALUE_REQUIRED, 'ServiceTitan Tenant ID')
            ->addOption(
                'endpoint',
                null,
                InputOption::VALUE_OPTIONAL,
                sprintf('API endpoint to test. Available: %s', implode(', ', array_keys(self::TEST_ENDPOINTS))),
                'customers'  // Changed default to a known working endpoint
            )
            ->addOption(
                'custom-endpoint',
                null,
                InputOption::VALUE_OPTIONAL,
                'Custom API endpoint path (overrides --endpoint)'
            )
            ->addOption(
                'production',
                null,
                InputOption::VALUE_NONE,
                'Use production environment instead of integration'
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Limit number of records to display',
                '5'
            )
            ->addOption(
                'json',
                null,
                InputOption::VALUE_NONE,
                'Output results as JSON'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Starting ServiceTitan API Test');

        // Get credentials
        $credentials = $this->getCredentials($input);
        if (!$credentials) {
            return Command::FAILURE;
        }

        try {
            // Test authentication directly with OAuth
            $this->logger->info('Testing Authentication');

            $environment = $input->getOption('production')
                ? ServiceTitanEnvironment::PRODUCTION
                : ServiceTitanEnvironment::INTEGRATION;

            $authResult = $this->authenticateDirectly(
                $credentials['client_id'],
                $credentials['client_secret'],
                $environment
            );

            if (!$authResult->isSuccess()) {
                $this->logger->error('Authentication failed', ['error' => $authResult->error]);
                return Command::FAILURE;
            }

            $this->logger->info('Authentication successful', [
                'access_token_preview' => substr($authResult->accessToken, 0, 20) . '...',
                'expires_at' => $authResult->expiresAt?->format('Y-m-d H:i:s') ?? 'Unknown',
            ]);

            // Test API endpoint
            $this->logger->info('Testing API Endpoint');
            $this->testApiEndpoint(
                $authResult->accessToken,
                $credentials['app_key'],
                $credentials['tenant_id'],
                $environment,
                $input
            );

            $this->logger->info('ServiceTitan API test completed successfully');
            return Command::SUCCESS;
        } catch (ServiceTitanApiException $e) {
            $this->logger->error('ServiceTitan API test failed', [
                'exception' => $e->getMessage(),
                'context' => $e->getContext(),
            ]);
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->logger->error('ServiceTitan API test failed with unexpected error', [
                'exception' => $e->getMessage(),
            ]);
            return Command::FAILURE;
        }
    }

    private function getCredentials(InputInterface $input): ?array
    {
        $clientId = $this->serviceTitanClientId;
        $clientSecret = $this->serviceTitanClientSecret;
        $appKey = $this->serviceTitanAppKey;
        $tenantId = $input->getOption('tenant-id') ?? $_ENV['SERVICETITAN_TENANT_ID'] ?? null;

        // Validate all required parameters
        if (!$tenantId) {
            $this->logger->error(
                'Missing Tenant Id. Please provide it via --tenant-id option or SERVICETITAN_TENANT_ID environment variable.'
            );
            return null;
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'app_key' => $appKey,
            'tenant_id' => $tenantId,
        ];
    }

    private function authenticateDirectly(
        string $clientId,
        string $clientSecret,
        ServiceTitanEnvironment $environment
    ): OAuthResult {
        $this->logger->info('Authenticating with ServiceTitan...', [
            'environment' => $environment->value,
        ]);

        // Determine auth URL based on environment
        $authUrl = $environment === ServiceTitanEnvironment::PRODUCTION
            ? 'https://auth.servicetitan.io/connect/token'
            : 'https://auth-integration.servicetitan.io/connect/token';

        try {
            $response = $this->httpClient->request('POST', $authUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(false);

            if ($statusCode === 200 && isset($content['access_token'])) {
                $expiresAt = new \DateTime();
                $expiresAt->add(new \DateInterval(sprintf('PT%dS', $content['expires_in'] ?? 3600)));

                // The OAuthResult::success() method requires a non-null refresh token
                // For client_credentials grant, we typically don't get a refresh token
                // So we'll use the constructor directly
                return new OAuthResult(
                    success: true,
                    accessToken: $content['access_token'],
                    refreshToken: $content['refresh_token'] ?? null,
                    expiresAt: $expiresAt
                );
            }

            $error = $content['error_description'] ?? $content['error'] ?? 'Unknown authentication error';
            return OAuthResult::failure($error);
        } catch (\Exception $e) {
            $this->logger->error('Authentication request failed', [
                'error' => $e->getMessage(),
            ]);
            return OAuthResult::failure('Authentication request failed: ' . $e->getMessage());
        }
    }

    private function testApiEndpoint(
        string $accessToken,
        string $appKey,
        string $tenantId,
        ServiceTitanEnvironment $environment,
        InputInterface $input
    ): void {
        // Determine endpoint
        $customEndpoint = $input->getOption('custom-endpoint');
        if ($customEndpoint) {
            $endpoint = $customEndpoint;
            $this->logger->info("Testing custom endpoint", ['endpoint' => $endpoint]);
        } else {
            $endpointKey = $input->getOption('endpoint');
            if (!isset(self::TEST_ENDPOINTS[$endpointKey])) {
                $this->logger->warning("Unknown endpoint key, using 'companies' instead", ['endpoint_key' => $endpointKey]);
                $endpointKey = 'companies';
            }
            $endpoint = self::TEST_ENDPOINTS[$endpointKey];
            $this->logger->info("Testing endpoint", ['endpoint_key' => $endpointKey]);
        }

        // Replace tenant placeholder
        $endpoint = str_replace('{tenant}', $tenantId, $endpoint);

        // Build full URL
        $baseUrl = $environment === ServiceTitanEnvironment::PRODUCTION
            ? 'https://api.servicetitan.io'
            : 'https://api-integration.servicetitan.io';

        $url = $baseUrl . $endpoint;

        // Debug output
        $this->logger->debug("Full API URL", ['url' => $url]);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'ST-App-Key' => $appKey,
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'pageSize' => $input->getOption('limit'),
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);

            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logger->info('API call successful', ['status_code' => $statusCode]);

                // Log results
                if ($input->getOption('json')) {
                    $this->logger->info('API Response (JSON)', ['response' => $data]);
                } else {
                    $this->displayResults($data);
                }
            } else {
                $error = $data['message'] ?? $data['error'] ?? 'Unknown API error';
                $this->logger->error("API call failed", ['status_code' => $statusCode, 'error' => $error]);
            }
        } catch (\Exception $e) {
            $this->logger->error('API request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function displayResults(array $data): void
    {
        if (isset($data['data']) && is_array($data['data'])) {
            $records = $data['data'];
            $totalCount = count($records);

            $this->logger->info("Retrieved records", ['count' => $totalCount]);

            if ($totalCount > 0) {
                // Log first record structure
                $this->logger->info('First record structure', ['record' => $records[0]]);

                // Log summary of all records
                if ($totalCount > 1) {
                    $summary = [];
                    foreach ($records as $index => $record) {
                        if ($index >= 10) break; // Only log first 10
                        $id = $record['id'] ?? 'N/A';
                        $name = $record['name'] ?? $record['title'] ?? $record['displayName'] ?? 'N/A';
                        $summary[] = [
                            'index' => $index + 1,
                            'id' => $id,
                            'name' => $name
                        ];
                    }
                    $this->logger->info('Records summary', ['records' => $summary]);

                    if ($totalCount > 10) {
                        $this->logger->info('Additional records available', ['remaining' => $totalCount - 10]);
                    }
                }
            }

            // Log pagination info
            if (isset($data['hasMore']) && $data['hasMore']) {
                $this->logger->info('More records available. Use pagination parameters to retrieve additional data.');
            }
        } else {
            $this->logger->info('API Response', ['data' => $data]);
        }
    }
}
