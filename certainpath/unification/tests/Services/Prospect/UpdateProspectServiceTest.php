<?php

namespace App\Tests\Services\Prospect;

use App\DTO\Request\Prospect\UpdateProspectDoNotMailDTO;
use App\Entity\Prospect;
use App\Services\Prospect\UpdateProspectService;
use App\Tests\FunctionalTestCase;
use Doctrine\DBAL\Exception;

class UpdateProspectServiceTest extends FunctionalTestCase
{
    private UpdateProspectService $updateProspectService;
    private Prospect $testProspect;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->updateProspectService = $this->getUpdateProspectService();
        
        $company = $this->initializeCompany();
        $this->testProspect = $this->initializeProspect($company);
    }

    public function tearDown(): void
    {
        unset($this->faker);

        parent::tearDown();
    }

    /**
     */
    public function testUpdateProspectDoNotMailToTrue(): void
    {
        $prospectId = $this->testProspect->getId();
        
        // Verify initial state
        $this->assertFalse($this->testProspect->isDoNotMail());

        $dto = UpdateProspectDoNotMailDTO::fromBool(true);

        $updatedProspect = $this->updateProspectService->updateProspect($prospectId, $dto);

        $this->assertTrue($updatedProspect->isDoNotMail());
        $this->assertEquals($prospectId, $updatedProspect->getId());
    }

    /**
     */
    public function testUpdateProspectDoNotMailToFalse(): void
    {
        // Set up prospect with doNotMail = true
        $company = $this->initializeCompany();
        $prospect = $this->initializeProspect($company, doNotMail: true);
        $prospectId = $prospect->getId();
        
        // Verify initial state
        $this->assertTrue($prospect->isDoNotMail());

        $dto = UpdateProspectDoNotMailDTO::fromBool(false);

        $updatedProspect = $this->updateProspectService->updateProspect($prospectId, $dto);

        $this->assertFalse($updatedProspect->isDoNotMail());
        $this->assertEquals($prospectId, $updatedProspect->getId());
    }

    /**
     */
    public function testUpdateProspectWithNullDoNotMail(): void
    {
        $prospectId = $this->testProspect->getId();
        $originalDoNotMail = $this->testProspect->isDoNotMail();

        $dto = UpdateProspectDoNotMailDTO::fromBool(null);

        $updatedProspect = $this->updateProspectService->updateProspect($prospectId, $dto);

        $this->assertEquals($originalDoNotMail, $updatedProspect->isDoNotMail());
        $this->assertEquals($prospectId, $updatedProspect->getId());
    }

    private function getUpdateProspectService(): UpdateProspectService
    {
        return $this->getService(UpdateProspectService::class);
    }
}