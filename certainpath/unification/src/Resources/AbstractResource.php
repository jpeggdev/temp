<?php

namespace App\Resources;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

abstract class AbstractResource
{
    public function __construct(
        private readonly Manager $fractal
    ) {
    }

    public function transformCollection(iterable $entities, array $includes = []): array
    {
        $resource = new Collection($entities, $this->getTransformer());
        $this->fractal->parseIncludes($includes);
        return $this->fractal->createData($resource)->toArray()['data'];
    }

    public function transformItem(object $entity, array $includes = []): array
    {
        $resource = new Item($entity, $this->getTransformer());
        $this->fractal->parseIncludes($includes);
        return $this->fractal->createData($resource)->toArray()['data'];
    }

    abstract protected function getTransformer(): TransformerAbstract;
}
