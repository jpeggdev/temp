<?php

namespace App\DTO\Request\Campaign;

use Symfony\Component\Validator\Constraints as Assert;

class GetCampaignDetailsDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The campaignId field cannot be empty')]
        #[Assert\Positive(message:'The campaignId field must be a positive integer')]
        public int $campaignId,
    ) {
    }
}
