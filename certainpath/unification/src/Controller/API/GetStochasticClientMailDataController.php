<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\DTO\Request\StochasticClientMailDataQueryDTO;
use App\Services\GetStochasticClientMailDataService;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetStochasticClientMailDataController extends ApiController
{
    public function __construct(
        private readonly GetStochasticClientMailDataService $getStochasticClientDataService,
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(
        '/api/stochastic/client-mail-data',
        name: 'api_stochastic_client_mail_data',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] StochasticClientMailDataQueryDTO $queryDTO,
    ): Response {
        $serviceResult = $this->getStochasticClientDataService->getMailData($queryDTO);
        return $this->createJsonSuccessResponse($serviceResult['items'], $serviceResult['pagination']);
    }
}
