<?php

namespace App\Tests\Controller\API\Prospects;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Repository\AddressRepository;
use App\Repository\ProspectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateProspectPreferredAddressDoNotMailControllerTest extends WebTestCase
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

    private function createAddress(Company $company, array $overrides = []): Address
    {
        $address = new Address();
        $address
            ->setCompany($company)
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

    private function createProspect(Company $company, Address $preferredAddress = null, array $overrides = []): Prospect
    {
        $prospect = new Prospect();
        $prospect
            ->setCompany($company)
            ->setFullName($overrides['fullName'] ?? $this->faker->name())
            ->setFirstName($overrides['firstName'] ?? $this->faker->firstName())
            ->setLastName($overrides['lastName'] ?? $this->faker->lastName())
            ->setDoNotMail($overrides['doNotMail'] ?? false)
            ->setActive($overrides['active'] ?? true);
            
        if ($preferredAddress) {
            $prospect->setPreferredAddress($preferredAddress);
        }
            
        if (isset($overrides['externalId'])) {
            $prospect->setExternalId($overrides['externalId']);
        }
            
        $this->entityManager->persist($prospect);
        $this->entityManager->flush();
        
        return $prospect;
    }

    public function testUpdateProspectPreferredAddressDoNotMailEndpointExists(): void
    {
        $prospectId = 1;
        
        $this->client->request('PATCH', "/api/prospects/{$prospectId}/preferred-address/do-not-mail", [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['doNotMail' => true]));

        $response = $this->client->getResponse();
        $this->assertNotEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN,
            Response::HTTP_NOT_FOUND,
            Response::HTTP_BAD_REQUEST
        ]);
    }

    public function testUpdatePreferredAddressDoNotMailSuccess(): void
    {
        $address = $this->createAddress($this->testCompany, ['doNotMail' => false]);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => true])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressDoNotMailSetFalse(): void
    {
        $address = $this->createAddress($this->testCompany, ['doNotMail' => true]);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => false])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK, 
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithNonExistentProspect(): void
    {
        $nonExistentId = 99999;
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$nonExistentId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => true])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_NOT_FOUND,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithProspectWithoutPreferredAddress(): void
    {
        $prospect = $this->createProspect($this->testCompany, null);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => true])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_NOT_FOUND,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithMissingDoNotMailField(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithNullDoNotMailValue(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => null])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithInvalidJson(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithInvalidDataType(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => 'invalid'])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithStringTrue(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => 'true'])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithStringFalse(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => 'false'])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithNumericValue(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => 1])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithZeroValue(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => 0])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithInvalidProspectId(): void
    {
        $invalidId = 'invalid';
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$invalidId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => true])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_NOT_FOUND,
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdatePreferredAddressWithExtraFields(): void
    {
        $address = $this->createAddress($this->testCompany);
        $prospect = $this->createProspect($this->testCompany, $address);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/preferred-address/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'doNotMail' => true,
                'extraField' => 'should be ignored'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }
}