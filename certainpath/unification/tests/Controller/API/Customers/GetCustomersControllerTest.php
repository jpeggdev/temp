<?php

namespace App\Tests\Controller\API\Customers;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetCustomersControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetCustomersEndpointExists(): void
    {
        $this->client->request('GET', '/api/customers');

        $response = $this->client->getResponse();

        $this->assertNotEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }
}