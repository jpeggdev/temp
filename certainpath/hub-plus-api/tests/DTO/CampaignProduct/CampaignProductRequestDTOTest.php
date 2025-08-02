<?php

namespace App\Tests\DTO\CampaignProduct;

use App\DTO\CampaignProduct\CampaignProductRequestDTO;
use PHPUnit\Framework\TestCase;

class CampaignProductRequestDTOTest extends TestCase
{
    public function testRequestDTOProperties(): void
    {
        $dto = new CampaignProductRequestDTO();
        $dto->name = 'Test Product';
        $dto->type = 'service';
        $dto->description = 'Test Description';
        $dto->category = 'letters';
        $dto->subCategory = 'standard';
        $dto->format = 'Format A';
        $dto->prospectPrice = 100.0;
        $dto->customerPrice = 120.0;
        $dto->mailerDescription = 'Mailer Description';
        $dto->code = 'LTR';
        $dto->hasColoredStock = true;
        $dto->brand = 'Brand A';
        $dto->size = 'Size A';
        $dto->distributionMethod = 'direct_mail';
        $dto->targetAudience = 'prospects';

        $this->assertEquals('Test Product', $dto->name);
        $this->assertEquals('service', $dto->type);
        $this->assertEquals('Test Description', $dto->description);
        $this->assertEquals('letters', $dto->category);
        $this->assertEquals('standard', $dto->subCategory);
        $this->assertEquals('Format A', $dto->format);
        $this->assertEquals(100.0, $dto->prospectPrice);
        $this->assertEquals(120.0, $dto->customerPrice);
        $this->assertEquals('Mailer Description', $dto->mailerDescription);
        $this->assertEquals('LTR', $dto->code);
        $this->assertTrue($dto->hasColoredStock);
        $this->assertEquals('Brand A', $dto->brand);
        $this->assertEquals('Size A', $dto->size);
        $this->assertEquals('direct_mail', $dto->distributionMethod);
        $this->assertEquals('prospects', $dto->targetAudience);
    }
}
