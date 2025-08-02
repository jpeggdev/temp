<?php

namespace App\Resources;

use App\Transformers\BatchStatusTransformer;

class BatchStatusResource extends AbstractResource
{
    protected function getTransformer(): BatchStatusTransformer
    {
        return new BatchStatusTransformer();
    }
}
