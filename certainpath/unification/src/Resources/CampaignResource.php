<?php

namespace App\Resources;

use App\Transformers\CampaignTransformer;
use League\Fractal\Manager;

class CampaignResource extends AbstractResource
{
    public function __construct(
        Manager $fractal,
        private readonly ProspectFilterRuleResource $prospectFilterRuleResource
    ) {
        parent::__construct($fractal);
    }

    protected function getTransformer(): CampaignTransformer
    {
        return new CampaignTransformer($this->prospectFilterRuleResource);
    }
}
