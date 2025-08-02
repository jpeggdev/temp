<?php

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Prospect\UpdateStochasticProspectDoNotMailRequestDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\UpdateStochasticProspectDoNotMailService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class UpdateStochasticProspectDoNotMailController extends ApiController
{
    public function __construct(
        private readonly UpdateStochasticProspectDoNotMailService $updateProspectService,
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
        '/stochastic-prospects/{prospectId}/do-not-mail',
        name: 'api_stochastic_prospect_do_not_mail_update',
        methods: ['PATCH']
    )]
    public function __invoke(
        int $prospectId,
        #[MapRequestPayload] UpdateStochasticProspectDoNotMailRequestDTO $requestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $result = $this->updateProspectService->updateProspectDoNotMail(
            $prospectId,
            $requestDTO,
        );

        return $this->createSuccessResponse($result);
    }
}
