<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class GetEmailCampaignActivityLogsMetadataDTO extends BaseGetEmailCampaignActivityLogDTO
{
    public function __construct(
        #[Assert\Type('bool')]
        public readonly ?bool $isSent = null,
        #[Assert\Type('bool')]
        public readonly ?bool $isDelivered = null,
        #[Assert\Type('bool')]
        public readonly ?bool $isOpened = null,
        #[Assert\Type('bool')]
        public readonly ?bool $isClicked = null,
        #[Assert\Type('bool')]
        public readonly ?bool $isBounced = null,
        ?string $emailEventPeriodFilter = '',
    ) {
        parent::__construct($emailEventPeriodFilter);
    }
}
