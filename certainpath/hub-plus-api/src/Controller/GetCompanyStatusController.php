<?php

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\Exception\CompanyStatusRetrievalException;
use App\Service\Unification\GetCompanyStatusService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class GetCompanyStatusController extends ApiController
{
    public function __construct(
        private readonly GetCompanyStatusService $statusService,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws CompanyStatusRetrievalException
     * @throws ClientExceptionInterface
     */
    #[Route(
        '/company/status',
        name: 'api_company_status_get',
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $companyStatus = $this->statusService->getCompanyStatus(
            $loggedInUserDTO->getActiveCompany()->getIntacctId()
        );

        /*
         * Example $companyStatus->statusKeyValues:
         * {
         *  "Jobs in Progress":0,
         *  "Prospects to Process":0,
         *  "Members to Process":0,
         *  "Invoices to Process":0
         * }
         */
        $keyValues = $companyStatus->statusKeyValues;
        $data = [];
        foreach ($keyValues as $key => $value) {
            $data[] = [
                'name' => $key,
                'value' => $value,
            ];
        }

        return $this->createSuccessResponse(
            $data
        );
    }
}
