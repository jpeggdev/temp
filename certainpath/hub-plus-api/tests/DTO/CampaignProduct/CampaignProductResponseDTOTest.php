<?php

namespace App\Tests\DTO\CampaignProduct;

use App\DTO\CampaignProduct\CampaignProductResponseDTO;
use App\Entity\CampaignProduct;
use App\Repository\CampaignProductRepository;
use App\Tests\AbstractKernelTestCase;

class CampaignProductResponseDTOTest extends AbstractKernelTestCase
{
    private CampaignProductRepository $repository;

    public function setUp(): void
    {
        $this->repository = $this->getService(CampaignProductRepository::class);
    }

    public function testResponseDTOFromEntity(): void
    {
        $campaignProduct = new CampaignProduct();
        $campaignProduct->setName('Test Product');
        $campaignProduct->setType('service');
        $campaignProduct->setDescription('Test Description');
        $campaignProduct->setCode('LTR');
        $campaignProduct->setDistributionMethod('direct_mail');
        $campaignProduct->setMailerDescription('Mailer Description');
        $campaignProduct->setHasColoredStock(true);
        $campaignProduct->setCategory('letters');
        $campaignProduct->setSubCategory('standard');
        $campaignProduct->setFormat('Format A');
        $campaignProduct->setProspectPrice('100.0');
        $campaignProduct->setCustomerPrice('120.0');
        $campaignProduct->setBrand('Brand A');
        $campaignProduct->setSize('Size A');
        $campaignProduct->setTargetAudience('prospects');

        $campaignProduct = $this->repository->saveCampaignProduct($campaignProduct);

        $dto = CampaignProductResponseDTO::fromEntity($campaignProduct);

        $this->assertEquals('Test Product', $dto->name);
        $this->assertEquals('service', $dto->type);
        $this->assertEquals('Test Description', $dto->description);
        $this->assertEquals('LTR', $dto->code);
        $this->assertEquals('direct_mail', $dto->distributionMethod);
        $this->assertEquals('Mailer Description', $dto->mailerDescription);
        $this->assertTrue($dto->hasColoredStock);
        $this->assertEquals('letters', $dto->category);
        $this->assertEquals('standard', $dto->subCategory);
        $this->assertEquals('Format A', $dto->format);
        $this->assertEquals('100.0', $dto->prospectPrice);
        $this->assertEquals('120.0', $dto->customerPrice);
        $this->assertEquals('Brand A', $dto->brand);
        $this->assertEquals('Size A', $dto->size);
        $this->assertEquals('prospects', $dto->targetAudience);
    }
}
