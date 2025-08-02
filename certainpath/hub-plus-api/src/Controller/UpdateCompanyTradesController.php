<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Request\Company\UpdateCompanyTradeDTO;
use App\Entity\Company;
use App\Service\Company\UpdateCompanyTradeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateCompanyTradesController extends ApiController
{
    public function __construct(
        private readonly UpdateCompanyTradeService $updateCompanyTradeService,
    ) {
    }

    #[Route('/companies/{uuid}/trades', name: 'api_company_update_trades', methods: ['PUT'])]
    public function __invoke(Company $company, #[MapRequestPayload] UpdateCompanyTradeDTO $dto): Response
    {
        $updatedCompanyTrades = $this->updateCompanyTradeService->updateCompanyTrade($company, $dto);

        return $this->createSuccessResponse($updatedCompanyTrades);
    }
}
