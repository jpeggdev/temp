<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCompletedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\DomainException\CampaignIteration\CampaignIterationCannotBeCreatedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationWeekNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Resources\CampaignResource;
use App\Services\Campaign\CreateCampaignService;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class CreateCampaignController extends ApiController
{
    public function __construct(
        private readonly CreateCampaignService $campaignService,
        private readonly CampaignResource $campaignResource,
    ) {
    }

    /**
     * @throws ORMException
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CompanyNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignIterationNotFoundException
     * @throws CampaignAlreadyProcessingException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     * @throws CampaignIterationStatusNotFoundException
     */
    #[Route('/api/campaign/create', name: 'api_campaign_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateCampaignDTO $createCampaignRequestDTO,
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $includes = $paginationDTO->includes;
        $campaign = $this->campaignService->createCampaignSync($createCampaignRequestDTO);
        $campaignData = $this->campaignResource->transformItem($campaign, $includes);

        return $this->createJsonSuccessResponse($campaignData);
    }
}
