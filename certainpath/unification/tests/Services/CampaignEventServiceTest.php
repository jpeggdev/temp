<?php

namespace App\Tests\Services;

use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Tests\FunctionalTestCase;
use Doctrine\ORM\Exception\ORMException;

class CampaignEventServiceTest extends FunctionalTestCase
{
    /**
     * @throws ORMException
     * @throws CompanyNotFoundException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyProcessingException
     */
    public function testCampaignEventStatusLifecycle(): void
    {
        $this->initializeCompany();
        $createCampaignDto = $this->prepareCreateCampaignDTO();
        $campaign = $this->initializeCampaignAsync($createCampaignDto);

        $service = $this->getCampaignEventService();
        self::assertNotNull($service);

        $service->createCampaignPendingEvent($createCampaignDto, $campaign);
        self::assertTrue(
            $service->isCampaignPending(
                $createCampaignDto
            )
        );
        $service->createCampaignCreatedEvent($createCampaignDto, $campaign);
        self::assertTrue(
            $service->isCampaignCreated(
                $createCampaignDto
            )
        );
        self::assertFalse(
            $service->isCampaignPending(
                $createCampaignDto
            )
        );
    }
}
