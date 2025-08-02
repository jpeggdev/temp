<?php

declare(strict_types=1);

namespace App\Service\Employee;

use App\DTO\Request\UpdateEmployeeApplicationAccessDTO;
use App\Entity\ApplicationAccess;
use App\Entity\Employee;
use App\Exception\ApplicationAccessNotFoundException;
use App\Exception\ApplicationNotFoundException;
use App\Repository\ApplicationAccessRepository;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateEmployeeApplicationAccessService
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ApplicationAccessRepository $applicationAccessRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateEmployeeApplicationAccess(Employee $employee, UpdateEmployeeApplicationAccessDTO $dto): void
    {
        $application = $this->applicationRepository->find($dto->applicationId);

        if (!$application) {
            throw new ApplicationNotFoundException();
        }

        // Handle the case where `active` is true: Create the ApplicationAccess record
        if ($dto->active) {
            $existingAccess = $this->applicationAccessRepository->findOneBy(['employee' => $employee, 'application' => $application]);

            if (!$existingAccess) {
                $applicationAccess = new ApplicationAccess();
                $applicationAccess->setEmployee($employee);
                $applicationAccess->setApplication($application);

                $this->entityManager->persist($applicationAccess);
                $this->entityManager->flush();
            }
        } else {
            // Handle the case where `active` is false: Delete the ApplicationAccess record
            $applicationAccess = $this->applicationAccessRepository->findOneBy(['employee' => $employee, 'application' => $application]);

            if (!$applicationAccess) {
                throw new ApplicationAccessNotFoundException();
            }

            $this->entityManager->remove($applicationAccess);
            $this->entityManager->flush();
        }
    }
}
