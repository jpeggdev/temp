<?php

namespace App\Client;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SalesforceClient
{
    private HttpClientInterface $client;
    private string $instanceUrl;
    private string $accessToken;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        string $username,
        string $password,
    ) {
        $this->client = HttpClient::create();
        $this->authenticate($clientId, $clientSecret, $username, $password);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function authenticate(string $clientId, string $clientSecret, string $username, string $password): void
    {
        $response = $this->client->request('POST', 'https://login.salesforce.com/services/oauth2/token', [
            'body' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'username' => $username,
                'password' => $password,
            ],
        ]);

        $data = $response->toArray();
        $this->accessToken = $data['access_token'];
        $this->instanceUrl = $data['instance_url'];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function query(string $soql, ?int $limit = null): array
    {
        if ($limit) {
            $soql .= " LIMIT $limit";
        }

        $allRecords = [];
        $nextUrl = null;
        $totalSize = 0;
        $done = false;

        try {
            // Use POST request for complex queries to avoid URL length limitations
            $response = $this->client->request('GET', "$this->instanceUrl/services/data/v56.0/query", [
                'headers' => [
                    'Authorization' => "Bearer $this->accessToken",
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'query' => [
                    'q' => $soql,
                ],
            ]);

            $data = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            // Add better error handling to understand what went wrong
            $errorResponse = $e->getResponse();
            $errorContent = '';

            try {
                $errorData = $errorResponse->toArray(false);
                $errorContent = json_encode($errorData, JSON_PRETTY_PRINT);
            } catch (\Exception $jsonException) {
                $errorContent = $errorResponse->getContent(false);
            }

            throw new \RuntimeException(sprintf('Salesforce query failed with status %d. Error details: %s. Original query: %s', $errorResponse->getStatusCode(), $errorContent, $soql), 0, $e);
        }

        // Collect records from first page
        if (isset($data['records'])) {
            $allRecords = [...$allRecords, ...$data['records']];
        }

        $totalSize = $data['totalSize'] ?? 0;
        $done = $data['done'] ?? true;
        $nextUrl = $data['nextRecordsUrl'] ?? null;

        // Continue fetching if there are more records
        while (!$done && $nextUrl) {
            $response = $this->client->request('GET', $this->instanceUrl.$nextUrl, [
                'headers' => [
                    'Authorization' => "Bearer $this->accessToken",
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = $response->toArray();

            // Collect records from subsequent pages
            if (isset($data['records'])) {
                $allRecords = [...$allRecords, ...$data['records']];
            }

            $done = $data['done'] ?? true;
            $nextUrl = $data['nextRecordsUrl'] ?? null;
        }

        // Return consolidated response with all records
        return [
            'totalSize' => $totalSize,
            'done' => true, // Always true since we've fetched all records
            'records' => $allRecords,
        ];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCompanies(
        ?int $limit = null,
        ?string $intacctId = null,
    ): array {
        // Build the WHERE clause properly
        $whereConditions = [
            "Account_Status__c IN ('Active','Dropped')",
            'IntacctID__c != null',
        ];

        if ($intacctId) {
            $whereConditions[] = "IntacctID__c = '".$intacctId."'";
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Break down the complex query into more manageable parts
        $selectFields = [
            'Id', 'ParentId', 'OwnerId', 'Ownership', 'IntacctID__c', 'Name',
            'Account_Status__c', 'AccountID__c', 'Primary_Member__c', 'Primary_Member_Email__c',
            'Intacct_Contact_Email__c', 'Intacct_Contact_First_Name__c', 'Intacct_Contact_Last_Name__c',
            'Software_Subscription__c', 'Stochastic_Sales_Rep__c', 'Stochastic_Rank__c',
            'Stochastic_Marketing_Status__c', 'Stochastic_Last_Activity__c',
            'BillingStreet', 'BillingCity', 'BillingState', 'BillingPostalCode', 'BillingCountry',
            'BillingStateCode', 'BillingCountryCode', 'BillingAddress',
            'ShippingStreet', 'ShippingCity', 'ShippingState', 'ShippingPostalCode', 'ShippingCountry',
            'ShippingStateCode', 'ShippingCountryCode', 'ShippingAddress',
            'Website', 'PhotoUrl',
        ];

        $subquery = '(SELECT Membership_Type__c, Membership_Type_Detail__c, Status__c, Revenue_to_Date__c FROM Memberships__r)';

        $query = sprintf(
            'SELECT %s, %s FROM Account WHERE %s',
            implode(', ', $selectFields),
            $subquery,
            $whereClause
        );

        return $this->query($query, $limit);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getSalesforceUsers(?int $limit = null): array
    {
        $query = 'SELECT Id, CompanyName, Department, Title, Username, Name, FirstName, LastName, Phone, Email FROM User';

        return $this->query($query, $limit);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCompanyEmployees(?string $intacctId = null, ?int $limit = null): array
    {
        $intacctFilter = '';
        if ($intacctId) {
            $intacctFilter = " AND IntacctID_Contact__c = '".$intacctId."'";
        }

        $selectFields = [
            'AccountId', 'Stochastic_Contact__c', 'Active_Stochastic__c', 'Id',
            'FirstName', 'LastName', 'Email', 'Phone', 'Title', 'Contact_Type__c',
            'Account_status__c', 'Inactive_Contact__c', 'SSO_ID__c', 'HUB_Account_Suspended__c',
            'HUB_User_Type__c', 'HUB_Account__c', 'IntacctID_Contact__c', 'ReportsToId',
        ];

        $contactTypes = [
            'Finance/Back Office', 'Manager', 'Owner/GM', 'Marketing', 'HR/Recruiting', 'SGI Employee',
        ];

        $query = sprintf(
            'SELECT %s FROM Contact WHERE HUB_Account__c = true%s AND Contact_Type__c IN (\'%s\')',
            implode(', ', $selectFields),
            $intacctFilter,
            implode('\',\'', $contactTypes)
        );

        return $this->query($query, $limit);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function describeObject(string $objectName): array
    {
        $response = $this->client->request(
            'GET',
            "$this->instanceUrl/services/data/v56.0/sobjects/$objectName/describe",
            [
                'headers' => [
                    'Authorization' => "Bearer $this->accessToken",
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return $response->toArray();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getEmployeesForCompanyIntacctId(
        string $intacctId,
    ): array {
        return $this->getCompanyEmployees(
            $intacctId
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCompanyByIntacctId(string $intacctId): array
    {
        return $this->getCompanies(
            null,
            $intacctId
        );
    }
}
