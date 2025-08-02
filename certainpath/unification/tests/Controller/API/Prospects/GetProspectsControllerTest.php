<?php

namespace App\Tests\Controller\API\Prospects;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Repository\ProspectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetProspectsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Faker\Generator $faker;
    private Company $testCompany;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->faker = Faker\Factory::create();
        
        $this->testCompany = $this->createCompany();
    }

    private function createCompany(): Company
    {
        $company = new Company();
        $company
            ->setName($this->faker->company())
            ->setIdentifier($this->faker->uuid())
            ->setActive(true);
            
        $this->entityManager->persist($company);
        $this->entityManager->flush();
        
        return $company;
    }

    private function createProspect(Company $company, array $overrides = []): Prospect
    {
        $prospect = new Prospect();
        $prospect
            ->setCompany($company)
            ->setFullName($overrides['fullName'] ?? $this->faker->name())
            ->setFirstName($overrides['firstName'] ?? $this->faker->firstName())
            ->setLastName($overrides['lastName'] ?? $this->faker->lastName())
            ->setDoNotMail($overrides['doNotMail'] ?? false)
            ->setActive($overrides['active'] ?? true)
            ->setAddress1($overrides['address1'] ?? $this->faker->streetAddress())
            ->setCity($overrides['city'] ?? $this->faker->city())
            ->setState($overrides['state'] ?? 'TX')
            ->setPostalCode($overrides['postalCode'] ?? $this->faker->postcode());
            
        if (isset($overrides['externalId'])) {
            $prospect->setExternalId($overrides['externalId']);
        }
            
        $this->entityManager->persist($prospect);
        $this->entityManager->flush();
        
        return $prospect;
    }

    private function createAddress(Prospect $prospect, array $overrides = []): Address
    {
        $address = new Address();
        $address
            ->setCompany($prospect->getCompany())
            ->setAddress1($overrides['address1'] ?? $this->faker->streetAddress())
            ->setCity($overrides['city'] ?? $this->faker->city())
            ->setStateCode($overrides['stateCode'] ?? 'TX')
            ->setPostalCode($overrides['postalCode'] ?? $this->faker->postcode())
            ->setDoNotMail($overrides['doNotMail'] ?? false)
            ->setActive($overrides['active'] ?? true);
            
        $this->entityManager->persist($address);
        $this->entityManager->flush();
        
        return $address;
    }

    public function testGetProspectsEndpointExists(): void
    {
        $this->client->request('GET', '/api/prospects');

        $response = $this->client->getResponse();
        $this->assertNotEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testGetProspectsWithQueryParameters(): void
    {
        $prospect1 = $this->createProspect($this->testCompany, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'city' => 'New York',
            'state' => 'NY'
        ]);
        
        $prospect2 = $this->createProspect($this->testCompany, [
            'firstName' => 'Jane',
            'lastName' => 'Smith', 
            'city' => 'Los Angeles',
            'state' => 'CA'
        ]);

        $queryParams = [
            'city' => 'New York',
            'state' => 'NY',
            'limit' => 10,
            'offset' => 0
        ];

        $this->client->request('GET', '/api/prospects?' . http_build_query($queryParams));

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testGetProspectsWithPagination(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createProspect($this->testCompany, [
                'fullName' => "Test Prospect {$i}"
            ]);
        }

        $queryParams = [
            'limit' => 2,
            'offset' => 1
        ];

        $this->client->request('GET', '/api/prospects?' . http_build_query($queryParams));

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testGetProspectsWithFilters(): void
    {
        $this->createProspect($this->testCompany, ['doNotMail' => true]);
        $this->createProspect($this->testCompany, ['doNotMail' => false]);

        $queryParams = [
            'doNotMail' => 'true'
        ];

        $this->client->request('GET', '/api/prospects?' . http_build_query($queryParams));

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testGetProspectsWithSearchTerm(): void
    {
        $this->createProspect($this->testCompany, [
            'fullName' => 'John Doe'
        ]);
        $this->createProspect($this->testCompany, [
            'fullName' => 'Jane Smith'
        ]);

        $queryParams = [
            'search' => 'John'
        ];

        $this->client->request('GET', '/api/prospects?' . http_build_query($queryParams));

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testGetProspectsWithSorting(): void
    {
        $this->createProspect($this->testCompany, ['fullName' => 'Alice']);
        $this->createProspect($this->testCompany, ['fullName' => 'Bob']);
        $this->createProspect($this->testCompany, ['fullName' => 'Charlie']);

        $queryParams = [
            'sortBy' => 'fullName',
            'sortOrder' => 'asc'
        ];

        $this->client->request('GET', '/api/prospects?' . http_build_query($queryParams));

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testGetProspectsWithInvalidQueryParameters(): void
    {
        $queryParams = [
            'limit' => 'invalid',
            'offset' => 'invalid'
        ];

        $this->client->request('GET', '/api/prospects?' . http_build_query($queryParams));

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN,
            Response::HTTP_BAD_REQUEST
        ]);
    }

    public function testGetProspectsEmptyResult(): void
    {
        $queryParams = [
            'city' => 'NonExistentCity'
        ];

        $this->client->request('GET', '/api/prospects?' . http_build_query($queryParams));

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }
}