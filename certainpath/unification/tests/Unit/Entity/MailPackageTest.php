<?php

namespace App\Tests\Unit\Entity;

use App\Entity\MailPackage;
use App\Entity\Prospect;

class MailPackageTest extends AbstractEntityTestCase
{
    private MailPackage $mailPackage;

    public function setUp(): void
    {
        parent::setUp();
        $this->mailPackage = new MailPackage();
    }

    public function testGetterAndSetterForId(): void
    {
        $this->assertNull($this->mailPackage->getId());
    }

    public function testGetterAndSetterForName(): void
    {
        $name = 'Test Package';
        $this->mailPackage->setName($name);
        $this->assertEquals($name, $this->mailPackage->getName());
    }

    public function testGetterAndSetterForSeries(): void
    {
        $series = 'Test Series';
        $this->mailPackage->setSeries($series);
        $this->assertEquals($series, $this->mailPackage->getSeries());
    }

    public function testExternalIdTrait(): void
    {
        $externalId = 'external123';
        $this->mailPackage->setExternalId($externalId);
        $this->assertEquals($externalId, $this->mailPackage->getExternalId());
    }

    public function testTimestampableTrait(): void
    {
        $now = new \DateTimeImmutable();
        $this->mailPackage->setCreatedAt($now);
        $this->mailPackage->setUpdatedAt($now);

        $this->assertEquals($now, $this->mailPackage->getCreatedAt());
        $this->assertEquals($now, $this->mailPackage->getUpdatedAt());
    }
}