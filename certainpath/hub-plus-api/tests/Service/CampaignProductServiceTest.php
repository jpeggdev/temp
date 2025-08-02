<?php

namespace App\Tests\Service;

use App\DTO\CampaignProduct\CampaignProductRequestDTO;
use App\DTO\CampaignProduct\CampaignProductResponseDTO;
use App\Entity\CampaignProduct;
use App\Repository\CampaignProductRepository;
use App\Service\CampaignProductService;
use App\Tests\AbstractKernelTestCase;
use Doctrine\ORM\EntityNotFoundException;

class CampaignProductServiceTest extends AbstractKernelTestCase
{
    private CampaignProductRepository $repository;
    private CampaignProductService $service;

    public function setUp(): void
    {
        parent::setUp();
        /** @var CampaignProductRepository $campaignProductRepo */
        $campaignProductRepo = $this->getService(CampaignProductRepository::class);
        $this->repository = $campaignProductRepo;
        /** @var CampaignProductService $campaignProductService */
        $campaignProductService = $this->getService(CampaignProductService::class);
        $this->service = $campaignProductService;
    }

    public function testCreate(): void
    {
        $requestDTO = new CampaignProductRequestDTO();
        $requestDTO->name = 'Test Product';
        $requestDTO->type = 'service';
        $requestDTO->description = 'Description';
        $requestDTO->category = 'letters';
        $requestDTO->subCategory = 'standard';
        $requestDTO->format = 'Format A';
        $requestDTO->prospectPrice = 100.0;
        $requestDTO->customerPrice = 120.0;
        $requestDTO->mailerDescription = 'Mailer Description';
        $requestDTO->code = 'LTR';
        $requestDTO->hasColoredStock = true;
        $requestDTO->brand = 'Brand A';
        $requestDTO->size = 'Size A';
        $requestDTO->distributionMethod = 'direct_mail';
        $requestDTO->targetAudience = 'prospects';

        $responseDTO = $this->service->createCampaignProductFromRequestDTO($requestDTO);

        $this->assertInstanceOf(CampaignProductResponseDTO::class, $responseDTO);
        $this->assertEquals('Test Product', $responseDTO->name);
    }

    public function testGet(): void
    {
        $campaignProduct = $this->createCampaignProduct();
        $campaignProduct = $this->repository->saveCampaignProduct($campaignProduct);

        $responseDTO = $this->service->getCampaignProductResponseDTOById($campaignProduct->getId());

        $this->assertInstanceOf(CampaignProductResponseDTO::class, $responseDTO);
        $this->assertEquals('Test Product', $responseDTO->name);
    }

    public function testGetNotFound(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->service->getCampaignProductResponseDTOById(1);
    }

    public function testUpdate(): void
    {
        $campaignProduct = $this->createCampaignProduct();
        $campaignProduct->setName('Old Name');
        $campaignProduct = $this->repository->saveCampaignProduct($campaignProduct);

        $requestDTO = new CampaignProductRequestDTO();

        $requestDTO->name = 'Updated Name';
        $requestDTO->type = 'service';
        $requestDTO->description = 'Updated Description';
        $requestDTO->category = 'letters';
        $requestDTO->subCategory = 'standard';
        $requestDTO->format = 'Updated Format';
        $requestDTO->prospectPrice = 110.0;
        $requestDTO->customerPrice = 130.0;
        $requestDTO->mailerDescription = 'Updated Mailer Description';
        $requestDTO->code = 'UPD';
        $requestDTO->hasColoredStock = false;
        $requestDTO->brand = 'Updated Brand';
        $requestDTO->size = 'Updated Size';
        $requestDTO->distributionMethod = 'email';
        $requestDTO->targetAudience = 'customers';
        $responseDTO = $this->service->updateCampaignProductFromRequestDTO($campaignProduct->getId(), $requestDTO);

        $this->assertInstanceOf(CampaignProductResponseDTO::class, $responseDTO);
        $this->assertEquals('Updated Name', $responseDTO->name);
    }

    public function testDelete(): void
    {
        $campaignProduct = $this->createCampaignProduct();
        $campaignProduct = $this->repository->saveCampaignProduct($campaignProduct);

        $this->repository->removeCampaignProduct($campaignProduct);

        $this->assertTrue(true); // Assert no exceptions were thrown
    }

    private function createCampaignProduct(): CampaignProduct
    {
        $campaignProduct = new CampaignProduct();
        $campaignProduct->setName('Test Product');
        $campaignProduct->setType('service');
        $campaignProduct->setDescription('Description');
        $campaignProduct->setCategory('letters');
        $campaignProduct->setSubCategory('standard');
        $campaignProduct->setFormat('Format A');
        $campaignProduct->setProspectPrice((string) 100.0);
        $campaignProduct->setCustomerPrice((string) 120.0);
        $campaignProduct->setMailerDescription('Mailer Description');
        $campaignProduct->setCode('LTR');
        $campaignProduct->setHasColoredStock(true);
        $campaignProduct->setBrand('Brand A');
        $campaignProduct->setSize('Size A');
        $campaignProduct->setDistributionMethod('direct_mail');
        $campaignProduct->setTargetAudience('prospects');

        return $campaignProduct;
    }
}
