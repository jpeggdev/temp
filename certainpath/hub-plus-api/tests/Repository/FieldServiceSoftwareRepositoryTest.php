<?php

namespace App\Tests\Repository;

use App\Entity\FieldServiceSoftware;
use App\Tests\AbstractKernelTestCase;

class FieldServiceSoftwareRepositoryTest extends AbstractKernelTestCase
{
    public function testManageFieldServicesSoftware(): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM field_service_software'
        );
        $serviceTitan = FieldServiceSoftware::serviceTitan();
        $fieldEdge = FieldServiceSoftware::fieldEdge();
        $successWare = FieldServiceSoftware::successWare();
        $other = FieldServiceSoftware::other();

        $serviceTitanFromName = FieldServiceSoftware::fromName(
            $serviceTitan->getName()
        );
        self::assertTrue(
            $serviceTitan->is($serviceTitanFromName)
        );

        $this->fieldServiceSoftwareRepository->saveSoftware(
            $serviceTitan
        );
        $this->fieldServiceSoftwareRepository->saveSoftware(
            $fieldEdge
        );
        $this->fieldServiceSoftwareRepository->saveSoftware(
            $successWare
        );
        $this->fieldServiceSoftwareRepository->saveSoftware(
            $other
        );

        $retrievedServiceTitan = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );
        self::assertSame(
            $serviceTitan->getName(),
            $retrievedServiceTitan->getName()
        );
        self::assertTrue(
            $serviceTitan->is($retrievedServiceTitan)
        );
        $retrievedFieldEdge = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::fieldEdge()
        );
        self::assertSame(
            $fieldEdge->getName(),
            $retrievedFieldEdge->getName()
        );
        self::assertTrue(
            $fieldEdge->is($retrievedFieldEdge)
        );
        $retrievedSuccessWare = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::successWare()
        );
        self::assertSame(
            $successWare->getName(),
            $retrievedSuccessWare->getName()
        );
        self::assertTrue(
            $successWare->is($retrievedSuccessWare)
        );
        $retrievedOther = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::other()
        );
        self::assertSame(
            $other->getName(),
            $retrievedOther->getName()
        );
        self::assertTrue(
            $other->is($retrievedOther)
        );
    }

    public function testInitializeSoftware(): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM field_service_software'
        );
        self::assertNull(
            $this->fieldServiceSoftwareRepository->getSoftware(
                FieldServiceSoftware::serviceTitan()
            )
        );
        $this->fieldServiceSoftwareRepository->initializeSoftware();
        self::assertNotNull(
            $this->fieldServiceSoftwareRepository->getSoftware(
                FieldServiceSoftware::serviceTitan()
            )
        );
        self::assertNotNull(
            $this->fieldServiceSoftwareRepository->getSoftware(
                FieldServiceSoftware::fieldEdge()
            )
        );
        self::assertNotNull(
            $this->fieldServiceSoftwareRepository->getSoftware(
                FieldServiceSoftware::successWare()
            )
        );
        self::assertNotNull(
            $this->fieldServiceSoftwareRepository->getSoftware(
                FieldServiceSoftware::other()
            )
        );
        self::assertCount(
            4,
            $this->fieldServiceSoftwareRepository->getAllSoftware()
        );
        $this->fieldServiceSoftwareRepository->initializeSoftware();
        self::assertCount(
            4,
            $this->fieldServiceSoftwareRepository->getAllSoftware()
        );
    }
}
