<?php

declare(strict_types=1);

namespace App\Tests\Service\Company;

use App\DTO\Request\Company\CreateCompanyDTO;
use App\DTO\Response\Company\CreateCompanyResponseDTO;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Service\Company\CreateCompanyService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CreateCompanyServiceTest extends MockeryTestCase
{
    private CreateCompanyService $service;
    private CompanyRepository|Mockery\LegacyMockInterface|Mockery\MockInterface $companyRepository;
    private EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyRepository = \Mockery::mock(CompanyRepository::class);
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);

        $this->service = new CreateCompanyService(
            $this->companyRepository,
            $this->entityManager
        );
    }

    /**
     * @throws \Exception
     */
    public function testCreateCompanySuccess(): void
    {
        $createCompanyDTO = new CreateCompanyDTO(
            companyName: 'Acme Corporation',
            websiteUrl: 'https://www.acme.com',
            salesforceId: 'SF123456',
            intacctId: 'IA654321',
            companyEmail: 'contact@acme.com'
        );

        $this->companyRepository
            ->shouldReceive('findOneBy')
            ->andReturnNull()
            ->times(3);

        $this->entityManager
            ->shouldReceive('beginTransaction')
            ->once();

        $this->companyRepository
            ->shouldReceive('save')
            ->with(\Mockery::type(Company::class), true)
            ->once();

        $this->entityManager
            ->shouldReceive('commit')
            ->once();

        $this->entityManager
            ->shouldReceive('clear')
            ->once();

        $response = $this->service->createCompany($createCompanyDTO);

        $this->assertInstanceOf(CreateCompanyResponseDTO::class, $response);
        $this->assertEquals('Acme Corporation', $response->companyName);
        $this->assertEquals('https://www.acme.com', $response->websiteUrl);
        $this->assertEquals('SF123456', $response->salesforceId);
        $this->assertEquals('IA654321', $response->intacctId);
        $this->assertEquals('contact@acme.com', $response->companyEmail);
    }
}
