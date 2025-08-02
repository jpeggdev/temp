<?php

namespace App\Controller\API;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/health')]
class HealthCheckController extends ApiController
{
    #[Route('/check', name: 'api_health_check', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->createJsonSuccessResponse(
            [
                'status' => 'ok',
            ]
        );
    }
}
