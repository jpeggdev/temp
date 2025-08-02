<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\ServiceTitan\Service\ServiceTitanMetricsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/servicetitan')]
class ServiceTitanDashboardController extends ApiController
{
    public function __construct(
        private readonly ServiceTitanMetricsService $metricsService,
    ) {
    }

    #[Route(
        '/dashboard',
        name: 'api_servicetitan_dashboard_get',
        methods: ['GET']
    )]
    public function __invoke(LoggedInUserDTO $loggedInUserDTO): Response
    {
        $company = $loggedInUserDTO->getActiveCompany();

        $dashboardData = $this->metricsService->getDashboardMetrics($company);

        return $this->createSuccessResponse($dashboardData);
    }
}
