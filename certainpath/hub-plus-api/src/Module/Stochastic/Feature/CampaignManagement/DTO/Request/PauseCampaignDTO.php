<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class PauseCampaignDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Campaign ID should not be blank.')]
        #[Assert\Positive(message: 'Campaign ID must be a positive integer.')]
        public int $campaignId,
    ) {
    }
}
