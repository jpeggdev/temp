<?php

namespace App\Message\CampaignIterations;

class ResumeCampaignIterationsMessage
{
    public function __construct(
        public int $campaignId,
        public int $campaignEventPausedId
    ) {
    }
}
