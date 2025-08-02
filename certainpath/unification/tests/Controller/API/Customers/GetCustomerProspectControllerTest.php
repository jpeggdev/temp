<?php

namespace App\Tests\Controller\API\Customers;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetCustomerProspectControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        // Reset kernel state to ensure WebTestCase can control it
        if (isset(static::$kernel)) {
            static::$kernel = null;
        }

        // Reset the booted flag
        static::$booted = false;

        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up after test
        if (isset(static::$kernel)) {
            static::$kernel = null;
        }
        static::$booted = false;
    }

    public function testGetCustomerProspectEndpointExists(): void
    {
        $customerId = 1;

        $this->client->request('PATCH', "/api/customers/{$customerId}/prospect", [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['intacctId' => 'test-intacct-id']));

        $response = $this->client->getResponse();

        $this->assertNotEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_FORBIDDEN,
        ]);
    }
}
