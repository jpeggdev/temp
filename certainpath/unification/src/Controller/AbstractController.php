<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;

abstract class AbstractController extends SymfonyAbstractController
{
    protected function validateCompanyAccess(Company $company = null): void
    {
        if (
            $company instanceof Company &&
            $this->getUser() instanceof User &&
            $user = $this->getUser()
        ) {
            if ($this->isGranted(User::ROLE_SYSTEM_ADMIN)) {
                return;
            }

            if (!$user->getCompanies()->contains($company)) {
                return;
            }
        }

        throw new AccessDeniedException();
    }
}
