<?php

namespace App\Tests\Services;

use App\Entity\AbstractAddress;
use App\Entity\Address;
use App\Entity\Prospect;
use App\Entity\Company;
use App\Exceptions\AddressIsInvalid;
use App\Exceptions\PostProcessingServiceException;
use App\Services\PostProcessingService;
use App\Tests\FunctionalTestCase;
use App\ValueObjects\AddressObject;
use Doctrine\DBAL\Exception;
use function PHPUnit\Framework\assertFalse;

class PostProcessingServiceTest extends FunctionalTestCase
{
    /**
     * @throws Exception
     * @throws PostProcessingServiceException
     * @throws \Exception
     */
    public function testAcxiom3ProspectsSameAddress(): void
    {
        $filePath = __DIR__ . '/../Files/Acxiom_5_prospects_3_with_same_address1.csv';
        self::assertFileExists($filePath);

        if ($this->getGenericIngestRepository()->isLocalDatabase()) {
            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                'TRUNCATE TABLE prospects_stream RESTART IDENTITY'
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                'TRUNCATE TABLE invoices_stream RESTART IDENTITY'
            );

            $this->getGenericIngestRepository()->getDatabase()->executeStatement(
                file_get_contents(__DIR__ . '/../SQL/Acxiom_5_prospects_3_with_same_address1.sql')
            );

            $consumer = $this->getDatabaseConsumer();
            $consumer->setLimit(10);
            $consumer->setDeleteRemote(false);
            $result = $consumer->syncGenericIngestDatabase(true);
            self::assertTrue($result);

            $recordPostProcessor = $this->getPostProcessingService();
            $recordPostProcessor->setRecordLimit(10);
            $recordPostProcessor->processProspects();

            $prospects = $this->getProspectRepository()->findAll();

            self::assertCount(5, $prospects);

            $address1 = $this->getAddressRepository()->findOneBy([
                'externalId' => '6904wardcanyonrdcliftonaz75001'
            ]);
            self::assertCount(3, $address1->getProspects());
            // Of the related prospects, confirm which isPreferred
            foreach ($address1->getProspects() as $prospect) {
                if ($prospect->isPreferred()) {
                    self::assertSame(
                        'id.serenaporter6904wardcanyonrdcliftonaz75001',
                        $prospect->getExternalId()
                    );
                }
            }

            $address2 = $this->getAddressRepository()->findOneBy([
                'externalId' => '6905wardcanyonrdcliftonaz75001'
            ]);
            self::assertCount(1, $address2->getProspects());
            // Of the related prospects, confirm which isPreferred
            foreach ($address2->getProspects() as $prospect) {
                if ($prospect->isPreferred()) {
                    self::assertSame(
                        'id.ryanmortensen6905wardcanyonrdcliftonaz75001',
                        $prospect->getExternalId()
                    );
                }
            }

            $address3 = $this->getAddressRepository()->findOneBy([
                'externalId' => '6906wardcanyonrdcliftonaz75001'
            ]);
            self::assertCount(1, $address3->getProspects());
            // Of the related prospects, confirm which isPreferred
            foreach ($address3->getProspects() as $prospect) {
                if ($prospect->isPreferred()) {
                    self::assertSame(
                        'id.mikeporter6906wardcanyonrdcliftonaz75001',
                        $prospect->getExternalId()
                    );
                }
            }
        } else {
            $this->markTestSkipped("Test skipped: the getGenericIngestRepository does not reference a local database.");
        }
    }

    public function testPreventDuplicateAddressCreation(): void
    {
        /** @var Company $company */
        $company = $this->getCompanyRepository()->save($this->getCompany());

        $prospect1 = $this->getValidProspect();
        $prospect1
            ->setFullName('Prospect 1')
            ->setAddress1('1234 Main St.')
            ->setCity('New York')
            ->setPostalCode('12345')
            ->setCompany($company);
        $this->getProspectRepository()->save($prospect1);

        $prospect2 = clone $prospect1;
        $prospect2
            ->setFullName('Prospect 2');
        $this->getProspectRepository()->save($prospect2);

        $recordPostProcessor = $this->getPostProcessingService();
        $recordPostProcessor->setRecordLimit(100);
        $recordPostProcessor->processProspects();

        $countAddresses = $this->getAddressRepository()->count();

        self::assertSame(1, $countAddresses);
    }

    /**
     * @throws PostProcessingServiceException
     * @throws AddressIsInvalid
     */
    public function testRemapProspectByAddress(): void
    {
        /** @var Company $company */
        $company = $this->getCompanyRepository()->save($this->getCompany());
        /** @var Address $address */
        $address = $this->getAddress();

        $address
            ->setCompany($company)
            ->setVerifiedAt(date_create_immutable());

        /** @var Address $address */
        $address = $this->getAddressRepository()->save($address);

        $prospect1 = $this->getValidProspect();
        $prospect1
            ->setFullName('Prospect 1')
            ->setProcessedAt(date_create_immutable())
            ->addAddress($address)
            ->setPreferredAddress($address);
        $this->getProspectRepository()->save($prospect1);

        self::assertTrue($prospect1->isPreferred());

        $prospect2 = clone $prospect1;
        $prospect2
            ->setFullName('Prospect 2')
            ->setProcessedAt(null)
            ->removeAddress($address);
        $this->getProspectRepository()->save($prospect2);

        self::assertTrue($prospect2->isPreferred());

        $recordPostProcessor = $this->getPostProcessingService();
        $recordPostProcessor->processProspects();

        self::assertFalse($prospect1->isPreferred());
        self::assertTrue($prospect2->isPreferred());
    }

    /**
     * @throws PostProcessingServiceException
     */
    public function testExistingBusinessAddressCorrectedToResidential(): void
    {
        $addressRepository = $this->getAddressRepository();
        $prospectRepository = $this->getProspectRepository();

        $company = $this->initializeCompany('SM000210');
        $address = $this->initializeAddress($company);

        $address->setAddress1('4041 Southgate Blvd');
        $address->setAddress2('');
        $address->setCity('Lincoln');
        $address->setStateCode('NE');
        $address->setPostalCode('685064878');
        $address->setPostalCodeShort('68506');
        $address->setCreatedAt(new \DateTimeImmutable('2024-11-18 05:37:31'));
        $address->setVerifiedAt(new \DateTimeImmutable('2024-11-18 05:37:31'));
        $address->setBusiness(true); //some rogue process set it to true, incorrectly

        $prospect = $this->initializeProspect($company, $address);
        self::assertNotNull($prospect);

        $addressRepository->saveAddress($address);
        $prospectRepository->saveProspect($prospect);

        $address = $addressRepository->findOneByExternalId(
            $address->getExternalId()
        );

        //Verify the rogue process did its thing
        self::assertTrue($address->isBusiness());

        $processor = $this->getPostProcessingService();
        $processor->processProspect($prospect);

        $address = $addressRepository->findOneByExternalId(
            $address->getExternalId()
        );

        //Verify it got properly correct afterward
        self::assertFalse($address->isBusiness());

        $prospects = $address->getProspects();
        self::assertCount(1, $prospects);
    }

    /**
     * @throws PostProcessingServiceException
     */
    public function testProcessProspects(): void
    {
        $repository = $this->getProspectRepository();
        $repository->save($this->getValidProspect());
        $repository->save($this->getInvalidProspect());

        $recordPostProcessor = $this->getPostProcessingService();
        $recordPostProcessor->processProspects();

        self::assertTrue(true);
    }

    /**
     * @throws PostProcessingServiceException
     */
    public function testLongFormPostalCodeToAddressFromProspect(): void
    {
        $prospect = $this->getValidProspect();
        $prospect->setPostalCode('12345-6789');
        $prospect->setExternalId('postal-code-test');

        self::assertSame('12345-6789', $prospect->getPostalCode());
        self::assertSame('12345', $prospect->getPostalCodeShort());

        $this->getProspectRepository()->save($prospect);
        self::assertSame(
            0,
            $this->getAddressRepository()->count()
        );
        $postProcessingService = $this->getPostProcessingService();
        $postProcessingService->processProspects();

        $retrievedProspect = $this->getProspectRepository()->findOneBy([
            'externalId' => 'postal-code-test'
        ]);

        $address = $retrievedProspect->getPreferredAddress();
        self::assertSame(
            '12345-6789',
            $address->getPostalCode()
        );
        self::assertSame(
            '12345',
            $address->getPostalCodeShort()
        );
    }
    public function testingAddressVerifiedFromProspect(): void
    {
        $prospect = $this->getValidProspect();
        $prospect->setPostalCode('12345-6789');
        $prospect->setExternalId('postal-code-test');

        self::assertSame('12345-6789', $prospect->getPostalCode());
        self::assertSame('12345', $prospect->getPostalCodeShort());

        $this->getProspectRepository()->save($prospect);
        self::assertSame(
            0,
            $this->getAddressRepository()->count()
        );
        $postProcessingService = $this->getPostProcessingService();
        $postProcessingService->processProspects();

        $retrievedProspect = $this->getProspectRepository()->findOneBy([
            'externalId' => 'postal-code-test'
        ]);

        $address = $retrievedProspect->getPreferredAddress();
        self::assertNotNull(
            $address->getVerifiedAt()
        );
    }

    protected function getPostProcessingService(): PostProcessingService
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
            ->setAddress2('Suite 100')
            ;
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

    private function getAddressObject(): AddressObject
    {
        $addressObject = new AddressObject([
            'name' => 'CertainPath',
            'address1' => '15301 Spectrum Drive',
            'address2' => 'Suite 200',
            'city' => 'Addison',
            'stateCode' => 'TX',
            'postalCode' => '75001',
        ]);

        return $addressObject;
    }

    /**
     * @throws AddressIsInvalid
     */
    private function getAddress(): AbstractAddress
    {
        return (new Address())->fromValueObject($this->getAddressObject());
    }
}
