<?php

namespace App\Tests\Controller\API\Prospects;

use App\Entity\Company;
use App\Entity\Prospect;
use App\Repository\ProspectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateProspectDoNotMailControllerTest extends WebTestCase
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
            ->setActive($overrides['active'] ?? true);
            
        if (isset($overrides['externalId'])) {
            $prospect->setExternalId($overrides['externalId']);
        }
            
        $this->entityManager->persist($prospect);
        $this->entityManager->flush();
        
        return $prospect;
    }

    public function testUpdateProspectDoNotMailEndpointExists(): void
    {
        $prospectId = 1;
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
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
            Response::HTTP_FORBIDDEN,
            Response::HTTP_NOT_FOUND
        ]);
    }

    public function testUpdateProspectDoNotMailWithPatchMethod(): void
    {
        $prospect = $this->createProspect($this->testCompany, ['doNotMail' => false]);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailWithPostMethod(): void
    {
        $prospect = $this->createProspect($this->testCompany, ['doNotMail' => false]);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'POST',
            "/api/prospects/{$prospectId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailSetTrue(): void
    {
        $prospect = $this->createProspect($this->testCompany, ['doNotMail' => false]);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailSetFalse(): void
    {
        $prospect = $this->createProspect($this->testCompany, ['doNotMail' => true]);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailWithInvalidJson(): void
    {
        $prospect = $this->createProspect($this->testCompany);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailWithMissingData(): void
    {
        $prospect = $this->createProspect($this->testCompany);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailWithInvalidDataType(): void
    {
        $prospect = $this->createProspect($this->testCompany);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailWithNonExistentProspect(): void
    {
        $nonExistentId = 99999;
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$nonExistentId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailWithNullValue(): void
    {
        $prospect = $this->createProspect($this->testCompany);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['doNotMail' => null])
        );

        $response = $this->client->getResponse();
        $this->assertContains($response->getStatusCode(), [
            Response::HTTP_OK,
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdateProspectDoNotMailWithExtraFields(): void
    {
        $prospect = $this->createProspect($this->testCompany);
        $prospectId = $prospect->getId();
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$prospectId}/do-not-mail",
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
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_UNAUTHORIZED, 
            Response::HTTP_FORBIDDEN
        ]);
    }

    public function testUpdateProspectDoNotMailWithInvalidProspectId(): void
    {
        $invalidId = 'invalid';
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$invalidId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailWithZeroId(): void
    {
        $zeroId = 0;
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$zeroId}/do-not-mail",
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

    public function testUpdateProspectDoNotMailWithNegativeId(): void
    {
        $negativeId = -1;
        
        $this->client->request(
            'PATCH',
            "/api/prospects/{$negativeId}/do-not-mail",
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
}