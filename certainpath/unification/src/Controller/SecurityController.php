<?php

namespace App\Controller;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/app/switch_user/{switchUser}', name: 'app_switch_user')]
    public function switchUser(
        Request $request,
        string $switchUser
    ): Response {
        $request->getSession()->set('app_user', $switchUser);
        return $this->redirectToRoute('app_user_home');
    }

    #[Route('/app/_exit', name: 'app_exit_impersonation')]
    public function exitImpersonation(
        Request $request
    ): Response {
        if ($request->getSession()->has('app_user')) {
            $request->getSession()->remove('app_user');
        }
        return $this->redirectToRoute('app_user_home');
    }

    #[Route('/app/logout', name: 'app_logout')]
    public function logout(
        Security $security
    ): Response {
        $security->logout(false);
        return $this->redirectToRoute('logout');
    }
}
