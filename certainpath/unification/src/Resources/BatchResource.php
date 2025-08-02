<?php

namespace App\Resources;

use App\Repository\BatchRepository;
use App\Transformers\BatchTransformer;
use League\Fractal\Manager;

class BatchResource extends AbstractResource
{
    public function __construct(
        Manager $fractal,
        private readonly ProspectFilterRuleResource $prospectFilterRuleResource,
        private readonly BatchRepository $batchRepository
    ) {
        parent::__construct($fractal);
    }

    protected function getTransformer(): BatchTransformer
    {
        return new BatchTransformer(
            $this->prospectFilterRuleResource,
            $this->batchRepository
        );
    }
}
