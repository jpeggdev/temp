<?php

namespace App\Tests\Client;

use App\Tests\AbstractKernelTestCase;
use App\ValueObject\SalesforceFields;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SalesforceClientTest extends AbstractKernelTestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function testGetSalesforceCompanies(): void
    {
        $client = $this->getSalesforceClient();
        self::assertNotNull($client);

        $companies = $client->getCompanies();
        self::assertGreaterThanOrEqual(
            2467,
            $companies['totalSize'],
        );
        self::assertCount(
            $companies['totalSize'],
            $companies['records']
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    public function testGetSalesforceUsers(): void
    {
        $client = $this->getSalesforceClient();
        $coaches = $client->getSalesforceUsers();
        self::assertNotEmpty($coaches);
        self::assertGreaterThanOrEqual(
            256,
            $coaches['totalSize'],
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function testGetCompanyEmployees(): void
    {
        $client = $this->getSalesforceClient();
        $employees = $client->getCompanyEmployees();
        self::assertNotEmpty($employees);
        self::assertGreaterThanOrEqual(
            9809,
            $employees['totalSize'],
        );
        self::assertGreaterThanOrEqual(
            10057,
            count($employees['records'])
        );
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetEmployeesForRosterCompany(): void
    {
        $client = $this->getSalesforceClient();
        $employees = $client->getEmployeesForCompanyIntacctId(
            'ES38577'
        );
        self::assertCount(
            2,
            $employees['records']
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function testDescribeContact(): void
    {
        $client = $this->getSalesforceClient();
        $contactDescription = $client->describeObject('Contact');

        // Extract just the field names for easier viewing
        $fieldNames = array_map(
            static fn ($field) => $field['name'],
            $contactDescription['fields']
        );

        self::assertNotEmpty($fieldNames);
        self::assertSame(
            SalesforceFields::$contactFields,
            $fieldNames,
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function testDescribeCompany(): void
    {
        $client = $this->getSalesforceClient();
        $companyDescription = $client->describeObject('Account');

        // Extract just the field names for easier viewing
        $fieldNames = array_map(
            static fn ($field) => $field['name'],
            $companyDescription['fields']
        );

        self::assertNotEmpty($fieldNames);
        self::assertSame(
            SalesforceFields::$companyFields,
            $fieldNames,
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function testDescribeUser(): void
    {
        $client = $this->getSalesforceClient();
        $userDescription = $client->describeObject('User');

        // Extract just the field names for easier viewing
        $fieldNames = array_map(
            static fn ($field) => $field['name'],
            $userDescription['fields']
        );

        self::assertNotEmpty($fieldNames);
        self::assertSame(
            SalesforceFields::$userFields,
            $fieldNames,
        );
    }
}
