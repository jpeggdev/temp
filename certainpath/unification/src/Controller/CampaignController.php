<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Entity\Company;
use App\Entity\User;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Repository\BatchRepository;
use App\Repository\CampaignRepository;
use App\Repository\ProspectRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CampaignController extends AbstractController
{
    #[Route('/app/campaigns', name: 'app_campaigns')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $companies = $user->getCompanies();
        $campaigns = [];

        /** @var Company $company */
        foreach ($companies as $company) {
            /** @var Campaign $campaign */
            foreach ($company->getCampaigns() as $campaign) {
                $campaigns[] = $campaign;
            }
        }

        return $this->render('campaigns/index.html.twig', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * @throws CampaignNotFoundException
     */
    #[Route('/app/campaign/{campaignId}/iterations', name: 'app_campaign_iterations')]
    public function viewCampaignIterations(
        int $campaignId,
        CampaignRepository $campaignRepository,
    ): Response {
        $campaign = $campaignRepository->findOneById($campaignId);
        if (!$campaign) {
            throw new CampaignNotFoundException();
        }

        $criteria = Criteria::create()
            ->orderBy(['id' => Order::Ascending]);
        $iterations = $campaign->getCampaignIterations()->matching($criteria);

        return $this->render('campaigns/viewCampaignIterations.html.twig', [
            'company' => $campaign->getCompany(),
            'campaign' => $campaign,
            'campaignIterations' => $iterations,
        ]);
    }

    #[Route(
        '/app/campaign/{campaignId}/iteration/{campaignIterationId}/batches',
        name: 'app_list_campaign_iteration_batches'
    )]
    public function viewCampaignIterationBatches(
        int $campaignIterationId,
        BatchRepository $batchRepository
    ): Response {
        $batches = $batchRepository->fetchAllByCampaignIterationId($campaignIterationId);

        return $this->render('campaigns/viewCampaignIterationBatches.html.twig', [
            'batches' => $batches,
        ]);
    }

    /**
     * @throws BatchNotFoundException
     */
    #[Route(
        '/app/batch/{batchId}/prospects',
        name: 'app_list_batch_prospects'
    )]
    public function viewBatchProspects(
        int $batchId,
        BatchRepository $batchRepository,
        ProspectRepository $prospectRepository
    ): Response {
        $batch = $batchRepository->findById($batchId);
        if (!$batch) {
            throw new BatchNotFoundException();
        }

        $prospects = $prospectRepository->fetchAllByBatchId($batch->getId());

        return $this->render('campaigns/viewCampaignBatchProspects.html.twig', [
            'prospects' => $prospects,
        ]);
    }
}
