<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Voter;

use App\Security\Voter\RoleSuperAdminVoter;

class CampaignProductVoter extends RoleSuperAdminVoter
{
    public const string CAMPAIGN_PRODUCT_MANAGE = 'CAMPAIGN_PRODUCT_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::CAMPAIGN_PRODUCT_MANAGE === $attribute;
    }
}
