<?php

namespace App\Services\CampaignIterationWeek;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

readonly class BaseCampaignIterationWeekService
{
    public function getBatchProspectsSlice(
        Collection $prospects,
        int $campaignIterationWeek,
        array $mailingDropWeeks,
    ): ArrayCollection {
        $prospectsCount = $prospects->count();
        $mailingDropWeeksCount = count($mailingDropWeeks);
        $prospectsPerBatchCount = (int) floor($prospectsCount / $mailingDropWeeksCount);

        $offset = $prospectsPerBatchCount * array_search($campaignIterationWeek, $mailingDropWeeks, true);

        $limit = ($campaignIterationWeek === end($mailingDropWeeks))
            ? $prospectsCount - $offset
            : $prospectsPerBatchCount;

        return new ArrayCollection($prospects->slice($offset, $limit));
    }
}
