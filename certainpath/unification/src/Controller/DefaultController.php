<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    #[Route('/app', name: 'app_user_home')]
    public function userHome(): Response
    {
        if ($this->getUser()->getCompanies()->count() === 1) {
            return $this->redirectToRoute('app_manage_company', [
                'identifier' => $this->getUser()->getCompanies()->first()->getIdentifier()
            ]);
        }

        return $this->redirectToRoute('app_companies');
    }
}
