<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\IdentityQueryDTO;
use App\Exception\UnableToQueryIdentityException;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\ConfigurationException;
use Auth0\SDK\Exception\NetworkException;

class IdentityQueryService
{
    private Auth0 $auth0;

    public function __construct(
        string $auth0Domain,
        string $auth0MachineToMachineClientId,
        string $auth0MachineToMachineClientSecret,
        string $auth0Audience,
    ) {
        try {
            $configuration = new SdkConfiguration(
                strategy: SdkConfiguration::STRATEGY_API,
                domain: $auth0Domain,
                clientId: $auth0MachineToMachineClientId,
                clientSecret: $auth0MachineToMachineClientSecret,
                audience: [$auth0Audience],
            );
            $this->auth0 = new Auth0($configuration);
        } catch (ConfigurationException $e) {
            throw new UnableToQueryIdentityException(500, 'Unable to configure Auth0 SDK.', $e);
        }
    }

    public function findUserByEmail(string $email): ?IdentityQueryDTO
    {
        try {
            $response = $this->auth0->management()->users()->getAll([
                'q' => 'email:"'.$email.'"',
                'search_engine' => 'v3',
            ]);

            if (200 === $response->getStatusCode()) {
                $contents = json_decode($response->getBody()->getContents(), true);
                if (count($contents) > 0) {
                    return IdentityQueryDTO::fromArray($contents[0]);
                }
            }

            return null;
        } catch (NetworkException $e) {
            throw new UnableToQueryIdentityException(500, 'Unable to query Auth0 for existing user.', $e);
        }
    }
}
