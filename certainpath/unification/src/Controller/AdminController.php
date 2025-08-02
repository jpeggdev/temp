<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
        ]);
    }

    #[Route('/admin/companies', name: 'app_admin_companies')]
    public function listCompanies(
        CompanyRepository $companyRepository,
    ): Response {
        if ($this->isGranted(User::ROLE_SYSTEM_ADMIN)) {
            $companies = $companyRepository->fetchAll();
        } else {
            $companies = $this->getUser()->getCompanies();
        }

        return $this->render('admin/companies.html.twig', [
            'companies' => $companies,
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function listUsers(
        UserRepository $userRepository,
        CompanyRepository $companyRepository,
    ): Response {
        if ($this->isGranted(User::ROLE_SYSTEM_ADMIN)) {
            $users = $userRepository->fetchAll();
        } else {
            $users = $userRepository->findRelatedUsers($this->getUser());
        }


        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'controller_name' => 'AdminController',
        ]);
    }
}
