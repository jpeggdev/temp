<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\Unmanaged\GenericIngestRepository;
use App\Repository\UserRepository;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CompaniesController extends AbstractController
{
    #[Route('/app/companies', name: 'app_companies')]
    public function index(
        CompanyRepository $companyRepository,
        UserRepository $userRepository
    ): Response {
        $user = $this->getUser();

        return $this->render('companies/index.html.twig', [
            'companies' => $user->getCompanies(),
            'controller_name' => 'CompaniesController',
        ]);
    }

    #[Route('/app/company/{identifier}', name: 'app_manage_company')]
    public function manageCompany(
        CompanyRepository $companyRepository,
        GenericIngestRepository $genericIngestRepository,
        string $identifier
    ): Response {
        $company = $companyRepository->findOneBy(['identifier' => $identifier]);

        if (!$this->isGranted(User::ROLE_SYSTEM_ADMIN)) {
            if (
                !$company ||
                !$this->getUser()->getCompanies()->contains($company)
            ) {
                throw new AccessDeniedException();
            }
        }

        $membersCount = $genericIngestRepository->count(
            'members_stream',
            [
                'tenant' => $company->getExternalIdentifier()
            ]
        );

        $invoicesCount = $genericIngestRepository->count(
            'invoices_stream',
            [
                'tenant' => $company->getExternalIdentifier()
            ]
        );

        $lastMember = $genericIngestRepository->fetchLast(
            'members_stream',
            [
                'tenant' => $company->getExternalIdentifier()
            ]
        );

        $lastInvoice = $genericIngestRepository->fetchLast(
            'invoices_stream',
            [
                'tenant' => $company->getExternalIdentifier()
            ]
        );

        return $this->render('companies/manageCompany.html.twig', [
            'company' => $company,
            'membersCount' => $membersCount,
            'invoicesCount' => $invoicesCount,
            'lastMember' => $lastMember,
            'lastInvoice' => $lastInvoice,
        ]);
    }
}
