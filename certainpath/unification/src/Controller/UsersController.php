<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UsersController extends AbstractController
{
    #[Route('/app/user/{id}', name: 'app_manage_user', requirements: ['id' => '\d+'])]
    public function manageUser(
        UserRepository $userRepsitory,
        CompanyRepository $companyRepository,
        ?string $id = null
    ): Response {
        throw new \Exception('Route not implemented');
        if (empty($id)) {
            $user = $this->getUser();
        } else {
            if (!$this->isGranted(User::ROLE_ACCOUNT_ADMIN)) {
                throw new AccessDeniedException();
            }

            $user = $userRepsitory->find($id);
            if (!$user) {
                throw new AccessDeniedException();
            }

            if (
                $user->isSuperAdmin() &&
                (
                    !$this->isGranted(User::ROLE_SUPER_ADMIN) &&
                    $user->getUserIdentifier() !== $this->getUser()->getUserIdentifier()
                )
            ) {
                throw new AccessDeniedException();
            }

            if (
                $user->isSystemAdmin() &&
                (
                    !$this->isGranted(User::ROLE_SUPER_ADMIN) &&
                    $user->getUserIdentifier() !== $this->getUser()->getUserIdentifier()
                )
            ) {
                throw new AccessDeniedException();
            }

            if (
                $user->isCompanyAdmin() &&
                (
                    !$this->isGranted(User::ROLE_SYSTEM_ADMIN) &&
                    $user->getUserIdentifier() !== $this->getUser()->getUserIdentifier()
                )
            ) {
                throw new AccessDeniedException();
            }
        }

        return $this->render('users/manageUser.html.twig', [
            'user' => $user
        ]);
    }
}
