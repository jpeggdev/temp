<?php

namespace App\Transformers;

use App\Entity\Location;
use League\Fractal\TransformerAbstract;

class LocationTransformer extends TransformerAbstract
{

    public function transform(Location $location): array
    {
        return [
            'id' => $location->getId(),
            'name' => $location->getName(),
            'description' => $location->getDescription(),
            'postalCodes' => $location->getPostalCodes(),
            'isActive' => $location->isActive(),
        ];
    }
}
