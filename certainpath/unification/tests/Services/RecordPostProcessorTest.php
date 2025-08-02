<?php

namespace App\Tests\Services;

use App\Entity\Prospect;
use App\Entity\Company;
use App\Exceptions\PostProcessingServiceException;
use App\Services\PostProcessingService;
use App\Tests\FunctionalTestCase;

class RecordPostProcessorTest extends FunctionalTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $repository = $this->getProspectRepository();
        $repository->save($this->getValidProspect());
        $repository->save($this->getInvalidProspect());
    }

    /**
     * @throws PostProcessingServiceException
     */
    public function testProcessProspects(): void
    {
        $recordPostProcessor = $this->getRecordPostProcessor();
        $recordPostProcessor->processProspects();

        $this->assertTrue(true);
    }

    protected function getRecordPostProcessor(): PostProcessingService
    {
        return $this->getService(
            PostProcessingService::class
        );
    }

    private function getCompany(): Company
    {
        return $this->getCompanyRepository()->findOneBy([
            'identifier' => 'UNI1'
        ]);
    }

    private function getInvalidProspect(): Prospect
    {
        return (new Prospect())
            ->setFullName('First Last')
            ->setFirstName('First')
            ->setLastName('Last')
            ->setCity('New York')
            ->setState('NY')
            ->setPostalCode('00001')
            ->setCompany($this->getCompany())
            ->setAddress1('1234 Madeup Ln')
            ->setAddress2('Suite 100');
    }

    private function getValidProspect(): Prospect
    {
        return (new Prospect())
            ->setFullName('CertainPath')
            ->setFirstName('First')
            ->setLastName('Last')
            ->setCity('Addison')
            ->setState('TX')
            ->setPostalCode('75001')
            ->setCompany($this->getCompany())
            ->setAddress1('15301 Spectrum Drive')
            ->setAddress2('Suite 200');
    }
}
