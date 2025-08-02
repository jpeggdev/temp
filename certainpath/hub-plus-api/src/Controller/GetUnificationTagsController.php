<?php

namespace App\Controller;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\TagQueryDTO;
use App\Exception\TagsRetrievalException;
use App\Service\GetUnificationTagsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class GetUnificationTagsController extends ApiController
{
    public function __construct(
        private readonly GetUnificationTagsService $unificationTagsService,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TagsRetrievalException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route(
        '/company/tags',
        name: 'api_company_tags_get',
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        #[MapQueryString] TagQueryDTO $queryDto,
    ): Response {
        $queryDto->companyIdentifier = $loggedInUserDTO->getActiveCompany()->getIntacctId();
        $data = $this->unificationTagsService->getTags(
            $queryDto
        );

        return $this->createSuccessResponse(
            $data
        );
    }
}
