<?php

namespace App\Controller\API\Company;

use App\Controller\API\ApiController;
use App\DTO\Query\Tag\TagQueryDTO;
use App\Services\TagQueryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCompanyTagsController extends ApiController
{
    public function __construct(private readonly TagQueryService $tagQueryService)
    {
    }

    #[Route('/api/company/{identifier}/tags', name: 'api_company_tags_get', methods: ['GET'])]
    public function __invoke(
        string $identifier,
        #[MapQueryString] TagQueryDTO $queryDto = new TagQueryDTO()
    ): Response {
        $queryDto->companyIdentifier = $identifier;
        $tagsData = $this->tagQueryService->getTags(
            $queryDto
        );

        return $this->createJsonSuccessResponse(
            $tagsData['tags'],
            $tagsData
        );
    }
}
