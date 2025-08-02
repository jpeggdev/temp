<?php

namespace App\Tests\Controller\API\Customers;

use App\Entity\Company;
use App\Entity\Prospect;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateCustomerDoNotMailControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testUpdateCustomerDoNotMailEndpointExists(): void
    {
        $customerId = 1;
        
        $this->client->request(
            'PATCH',
            "/api/customers/{$customerId}/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => true])
        );

        $response = $this->client->getResponse();
        $this->assertNotEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }
}
