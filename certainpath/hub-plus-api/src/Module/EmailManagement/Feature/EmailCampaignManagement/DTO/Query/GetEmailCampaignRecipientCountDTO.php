<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class GetEmailCampaignRecipientCountDTO
{
    public function __construct(
        #[Assert\Type(type: 'integer', message: 'The eventSessionId field must be a valid integer.')]
        #[Assert\Positive(message: 'The eventSessionId field id must be a positive integer.')]
        public ?int $eventSessionId = null,
    ) {
    }
}
