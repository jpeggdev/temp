<?php

namespace App\Resources;

use App\Transformers\ProspectTransformer;

class ProspectResource extends AbstractResource
{
    protected function getTransformer(): ProspectTransformer
    {
        return new ProspectTransformer();
    }
}
