<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class BaseGetEmailCampaignActivityLogDTO
{
    public const string DATE_RANGE_TODAY = 'today';
    public const string DATE_RANGE_LAST_7_DAYS = 'last_7_days';
    public const string DATE_RANGE_LAST_30_DAYS = 'last_30_days';
    public const string DATE_RANGE_LAST_90_DAYS = 'last_90_days';

    public function __construct(
        #[Assert\Type(type: 'string', message: 'The email event period filter value is invalid.')]
        #[Assert\Choice(
            [
                self::DATE_RANGE_TODAY,
                self::DATE_RANGE_LAST_7_DAYS,
                self::DATE_RANGE_LAST_30_DAYS,
                self::DATE_RANGE_LAST_90_DAYS,
            ]
        )]
        public ?string $emailEventPeriodFilter = '',
    ) {
    }
}
