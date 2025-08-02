<?php

namespace App\Tests\Entity;

use App\Entity\CampaignProduct;
use App\Tests\AbstractKernelTestCase;
use PHPUnit\Framework\TestCase;

class CampaignProductTest extends AbstractKernelTestCase
{
    public function setUp(): void
    {
        $this->doInitializeProducts = true;
        parent::setUp();
    }

    public function testPricing(): void
    {
        $product = $this->campaignProductRepository->findOneActiveById(1);
        self::assertNotNull($product);
        $product->setProspectPrice('0.12345');
        $product->setCustomerPrice('0.54321');
        $this->campaignProductRepository->saveCampaignProduct($product);

        $this->entityManager->clear();

        $product = $this->campaignProductRepository->findOneActiveById(1);

        self::assertSame('0.12345', $product->getProspectPrice());
        self::assertSame('0.54321', $product->getCustomerPrice());
    }
}
