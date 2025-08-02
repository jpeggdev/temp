<?php

namespace App\Transformers;

use App\Entity\ProspectFilterRule;
use League\Fractal\TransformerAbstract;

class ProspectFilterRuleTransformer extends TransformerAbstract
{
    public function transform(ProspectFilterRule $prospectFilterRule): array
    {
        $data['id'] = $prospectFilterRule->getId();
        $data['name'] = $prospectFilterRule->getName();
        $data['displayed_name'] = $prospectFilterRule->getDisplayedName();
        $data['description'] = $prospectFilterRule->getDescription();

        return $data;
    }
}
