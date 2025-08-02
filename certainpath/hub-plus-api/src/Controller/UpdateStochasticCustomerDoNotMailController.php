<?php

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Customer\UpdateStochasticCustomerDoNotMailRequestDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\UpdateStochasticCustomerDoNotMailService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class UpdateStochasticCustomerDoNotMailController extends ApiController
{
    public function __construct(
        private readonly UpdateStochasticCustomerDoNotMailService $updateCustomerDoNotMailService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route(
        '/stochastic-customers/{customerId}/do-not-mail',
        name: 'api_stochastic_customer_do_not_mail_update',
        methods: ['PATCH']
    )]
    public function __invoke(
        int $customerId,
        #[MapRequestPayload] UpdateStochasticCustomerDoNotMailRequestDTO $requestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $intacctId = $loggedInUserDTO->getActiveCompany()->getIntacctId();

        $result = $this->updateCustomerDoNotMailService->updateCustomerDoNotMail(
            $customerId,
            $requestDTO,
            $intacctId
        );

        return $this->createSuccessResponse($result);
    }
}
