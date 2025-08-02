<?php

namespace App\Resources;

use App\Transformers\BulkBatchStatusEventTransformer;

class BulkBatchStatusEventResource extends AbstractResource
{
    protected function getTransformer(): BulkBatchStatusEventTransformer
    {
        return new BulkBatchStatusEventTransformer();
    }
}
