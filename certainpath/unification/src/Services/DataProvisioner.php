<?php

namespace App\Services;

use App\Entity\Company;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;

class DataProvisioner
{
    private static array $users = [
        [
            'identifier' => 'kharrington@mycertainpath.com',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'companies' => [
                [
                    'identifier' => 'UNI1',
                    'name' => 'Unification',
                ],
                [
                    'identifier' => 'CPA1',
                    'name' => 'CertainPath',
                ],
            ],
        ],
        [
            'identifier' => 'cholland@mycertainpath.com',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'companies' => [
                [
                    'identifier' => 'UNI1',
                    'name' => 'Unification',
                ],
            ],
        ],
        [
            'identifier' => 'dbeard@mycertainpath.com',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'companies' => [
                [
                    'identifier' => 'UNI1',
                    'name' => 'Unification',
                ],
            ],
        ],
        [
            'identifier' => 'mpatten@mycertainpath.com',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'companies' => [
                [
                    'identifier' => 'UNI1',
                    'name' => 'Unification',
                ],
            ],
        ],
        [
            'identifier' => 'jcrawmer@mycertainpath.com',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'companies' => [
                [
                    'identifier' => 'UNI1',
                    'name' => 'Unification',
                ],
            ],
        ],
        [
            'identifier' => 'lbalandin@mycertainpath.com',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'companies' => [
                [
                    'identifier' => 'UNI1',
                    'name' => 'Unification',
                ],
            ],
        ],
        [
            'identifier' => 'tony@stochasticmkt.com',
            'roles' => [User::ROLE_SYSTEM_ADMIN],
            'companies' => [
                [
                    'identifier' => 'STO1',
                    'name' => 'Unification',
                ],
            ],
        ],
        [
            'identifier' => 'systemadmin1@stochastic.com',
            'roles' => [User::ROLE_SYSTEM_ADMIN],
            'companies' => [
                [
                    'identifier' => 'STO1',
                    'name' => 'Stochastic',
                ],
            ],
        ],
        [
            'identifier' => 'companyadmin1@company.com',
            'roles' => [User::ROLE_ACCOUNT_ADMIN],
            'companies' => [
                [
                    'identifier' => 'COM1',
                    'name' => 'First Company Name',
                ],
                [
                    'identifier' => 'COM2',
                    'name' => 'Second Company Name',
                ],
                [
                    'identifier' => 'COM3',
                    'name' => 'Third Company Name',
                ],
            ],
        ],
        [
            'identifier' => 'user1@company.com',
            'roles' => [User::ROLE_USER],
            'companies' => [
                [
                    'identifier' => 'COM1',
                    'name' => 'First Company Name',
                ],
            ],
        ],
    ];

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CompanyRepository $companyRepository,
    ) {
    }

    public function populateWorkingData(): void
    {
        foreach (self::$users as $_user) {
            $user = $this->userRepository->findOneByIdentifier($_user['identifier']);
            if (!$user) {
                $user = new User();
            }

            $user->setAccessRoles([ ]);
            foreach ($_user['roles'] as $role) {
                $user->addAccessRole($role);
            }

            $user->setIdentifier($_user['identifier']);
            $user->removeAllCompanies();
            $this->userRepository->save($user);

            foreach ($_user['companies'] as $_company) {
                $company = $this->companyRepository->findOneBy(['identifier' => $_company['identifier']]);
                if (!$company) {
                    $company = new Company();
                }

                $company->setIdentifier($_company['identifier'])
                    ->setName($_company['name'])
                    ->addUser($user);
                $this->companyRepository->save($company);
            }

            $this->userRepository->save($user);
        }
    }
}
