<?php

namespace App\Resources;

use App\Transformers\ProspectFilterRuleTransformer;

class ProspectFilterRuleResource extends AbstractResource
{
    protected function getTransformer(): ProspectFilterRuleTransformer
    {
        return new ProspectFilterRuleTransformer();
    }
}
