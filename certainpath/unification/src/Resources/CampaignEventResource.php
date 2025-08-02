<?php

namespace App\Resources;

use App\Repository\BatchRepository;
use App\Transformers\BatchTransformer;
use App\Transformers\CampaignEventTransformer;
use League\Fractal\Manager;

class CampaignEventResource extends AbstractResource
{
    public function __construct(
        Manager $fractal,
        private readonly EventStatusResource $eventStatusResource
    ) {
        parent::__construct($fractal);
    }

    protected function getTransformer(): CampaignEventTransformer
    {
        return new CampaignEventTransformer($this->eventStatusResource);
    }
}
