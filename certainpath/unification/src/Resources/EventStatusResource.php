<?php

namespace App\Resources;

use App\Transformers\EventStatusTransformer;

class EventStatusResource extends AbstractResource
{
    protected function getTransformer(): EventStatusTransformer
    {
        return new EventStatusTransformer();
    }
}
