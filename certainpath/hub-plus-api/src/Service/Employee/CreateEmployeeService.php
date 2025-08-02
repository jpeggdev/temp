<?php

declare(strict_types=1);

namespace App\Service\Employee;

use App\DTO\Request\Employee\CreateEmployeeDTO;
use App\DTO\Response\CreateUserResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\User;
use App\Exception\EmployeeAlreadyExistsException;
use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use App\Repository\UserRepository;
use App\Service\ApplicationSignalingService;
use App\Service\IdentityCreationService;
use App\Service\IdentityDeletionService;
use App\Service\IdentityQueryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

readonly class CreateEmployeeService
{
    public function __construct(
        private UserRepository $userRepository,
        private EmployeeRepository $employeeRepository,
        private EntityManagerInterface $entityManager,
        private IdentityQueryService $identityQueryService,
        private IdentityCreationService $identityCreationService,
        private IdentityDeletionService $identityDeletionService,
        private CompanyRepository $companyRepository,
        private ApplicationSignalingService $signal,
    ) {
    }

    public function createEmployee(
        CreateEmployeeDTO $createEmployeeDTO,
        Company $company,
    ): CreateUserResponseDTO {
        $this->entityManager->beginTransaction();

        $auth0UserCreated = false;
        try {
            $company = $this->companyRepository->findOneByIdentifier(
                $company->getIntacctId()
            );
            $auth0User = $this->identityQueryService->findUserByEmail($createEmployeeDTO->email);

            $this->signal->console(
                'Creating: '
                .$createEmployeeDTO->email
                .' / '
                .$createEmployeeDTO->firstName
                .' / '
                .$createEmployeeDTO->lastName
                .' / auth0 Email: '
                .$auth0User?->email
                .' / auth0 First Name: '
                .$auth0User?->firstName
                .' / auth0 Last Name: '
                .$auth0User?->lastName
            );

            if ($auth0User) {
                $user = $this->userRepository->findOneByEmail($auth0User->email);
                if (!$user) {
                    $conflictingUserWithSSOId = $this->userRepository->findOneBySsoId($auth0User->id);
                    if ($conflictingUserWithSSOId) {
                        $this->signal->console(
                            'Found user:'
                            .$conflictingUserWithSSOId->getEmail()
                            .' conflicting with '
                            .$auth0User->email
                            .' and resetting its SSO ID to null'
                        );
                        $conflictingUserWithSSOId->setSsoId(null);
                        $this->userRepository->save($conflictingUserWithSSOId, true);
                    }
                }

                if ($user) {
                    if ($user->getSsoId() !== $auth0User->id) {
                        $user->setSsoId($auth0User->id);
                        $this->userRepository->save($user, true);
                    }
                } else {
                    $user = new User();
                    $user->setFirstName($createEmployeeDTO->firstName);
                    $user->setLastName($createEmployeeDTO->lastName);
                    $user->setEmail($auth0User->email);
                    $user->setSsoId($auth0User->id);
                    $this->userRepository->save($user, true);
                }
            } else {
                $this->signal->console(
                    'No auth0 user found for email: '.$createEmployeeDTO->email
                );
                $identity = $this->identityCreationService->createIdentity($createEmployeeDTO);
                $auth0UserCreated = true;

                $user = new User();
                $user->setFirstName($createEmployeeDTO->firstName);
                $user->setLastName($createEmployeeDTO->lastName);
                $user->setEmail($createEmployeeDTO->email);
                $user->setSsoId($identity->id);
                $this->userRepository->save($user, true);
            }

            $existingEmployee = $this->employeeRepository->findOneBy([
                'user' => $user,
                'company' => $company,
            ]);

            if ($existingEmployee) {
                throw new EmployeeAlreadyExistsException();
            }

            $newEmployee = new Employee();
            $newEmployee->setUser($user);
            $newEmployee->setCompany($company);
            $newEmployee->setFirstName($createEmployeeDTO->firstName);
            $newEmployee->setLastName($createEmployeeDTO->lastName);
            $this->employeeRepository->save($newEmployee, true);

            $this->entityManager->commit();

            return new CreateUserResponseDTO(
                $user->getId(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getEmail(),
                $newEmployee->getUuid(),
                $user->getSalesforceId()
            );
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            // Let's delete the auth0 user if there were errors on the creation of the employee
            // Only if we were responsible for creating the auth0 user during the execution of this method
            if ($auth0UserCreated) {
                $this->identityDeletionService->deleteIdentity($identity->id);
            }

            throw $e;
        } finally {
            $this->entityManager->clear();
        }
    }

    public function setSignaling(OutputInterface $output): void
    {
        $this->signal->setOutput($output);
    }
}
