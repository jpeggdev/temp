<?php

namespace App\Service;

use App\DTO\LoggedInUserDTO;
use App\Entity\Company;
use App\Entity\User;
use App\Exception\NoAuthenticatedUserFoundException;
use App\Exception\NoEmployeeRecordsFoundException;
use App\Exception\NotFoundException\CompanyNotFoundException;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class GetLoggedInUserDTOService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private RequestStack $requestStack,
        private EmployeeRepository $employeeRepository,
    ) {
    }

    public function getLoggedInUserDTO(): ?LoggedInUserDTO
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null; // No request available, cannot determine user
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new NoAuthenticatedUserFoundException();
        }

        $companyUuid = $request->headers->get('X-Company-UUID');

        if ($companyUuid) {
            $company = $this->entityManager->getRepository(Company::class)->findOneBy(['uuid' => $companyUuid]);

            if (!$company) {
                throw new CompanyNotFoundException();
            }

            $employeeForCompany = $this->employeeRepository->findEmployeeForCompany($user, $companyUuid);

            if ($employeeForCompany) {
                $activeEmployee = $employeeForCompany;
                $activeCompany = $company;
            } else {
                $certainPathEmployee = $this->employeeRepository->findCertainPathEmployee($user);

                if ($certainPathEmployee) {
                    $activeEmployee = $certainPathEmployee;
                    $activeCompany = $company;
                } else {
                    throw new NoEmployeeRecordsFoundException();
                }
            }
        } else {
            $activeEmployee = $this->employeeRepository->findFirstEmployeeForUser($user);

            if (!$activeEmployee) {
                throw new NoEmployeeRecordsFoundException('No employee records found for the user.');
            }

            $activeCompany = $activeEmployee->getCompany();
        }

        return new LoggedInUserDTO($user, $activeEmployee, $activeCompany);
    }
}
