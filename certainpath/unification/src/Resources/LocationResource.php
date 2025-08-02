<?php

namespace App\Resources;

use App\Transformers\LocationTransformer;

class LocationResource extends AbstractResource
{
    protected function getTransformer(): LocationTransformer
    {
        return new LocationTransformer();
    }
}
