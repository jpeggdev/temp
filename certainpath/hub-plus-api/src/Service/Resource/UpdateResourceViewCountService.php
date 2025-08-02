<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateResourceViewCountService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function incrementViewCount(Resource $resource): void
    {
        $resource->incrementViewCount();
        $this->em->flush();
    }
}
