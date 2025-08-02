<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Voter;

use App\Module\Stochastic\Feature\CampaignManagement\Service\GetCampaignService;
use App\Service\GetLoggedInUserDTOService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, int>
 */
class CampaignDetailsVoter extends Voter
{
    public const string VIEW = 'CAMPAIGN_VIEW';

    public function __construct(
        private readonly GetCampaignService $getCampaignService,
        private readonly GetLoggedInUserDTOService $getLoggedInUserDTOService,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VIEW === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        $loggedInUser = $this->getLoggedInUserDTOService->getLoggedInUserDTO();
        $activeCompanyIntacctId = $loggedInUser->getActiveCompany()->getIntacctId();

        if (!$activeCompanyIntacctId) {
            return false;
        }

        if (is_int($subject)) {
            try {
                $campaign = $this->getCampaignService->getCampaign($subject);

                return
                    $campaign->companyId !== $subject
                    && $campaign->intacctId === $activeCompanyIntacctId
                ;
            } catch (\Throwable $e) {
                return false;
            }
        }

        return false;
    }
}
