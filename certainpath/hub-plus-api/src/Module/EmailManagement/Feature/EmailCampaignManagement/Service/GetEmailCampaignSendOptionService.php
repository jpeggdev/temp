<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Service;

readonly class GetEmailCampaignSendOptionService
{
    public const string SEND_OPTION_SAVE_AS_DRAFT = 'Save as Draft';
    public const string SEND_OPTION_SEND_IMMEDIATELY = 'Send Immediately';
    public const string SEND_OPTION_SCHEDULE_FOR_LATER = 'Schedule For Later';

    public function getSendOptions(): array
    {
        return [
            [
                'id' => 1,
                'label' => self::SEND_OPTION_SAVE_AS_DRAFT,
            ],
            [
                'id' => 2,
                'label' => self::SEND_OPTION_SEND_IMMEDIATELY,
            ],
            //            TODO: Uncomment once the feature is implemented
            //            [
            //                'id' => 3,
            //                'label' => self::SEND_OPTION_SCHEDULE_FOR_LATER,
            //            ],
        ];
    }
}
