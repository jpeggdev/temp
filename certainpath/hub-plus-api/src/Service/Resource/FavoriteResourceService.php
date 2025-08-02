<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\Entity\Employee;
use App\Entity\Resource;
use App\Entity\ResourceFavorite;
use App\Repository\ResourceFavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class FavoriteResourceService
{
    public function __construct(
        private ResourceFavoriteRepository $resourceFavoriteRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function toggleFavorite(Resource $resource, Employee $employee): bool
    {
        $existingFavorite = $this->resourceFavoriteRepository->findOneBy([
            'employee' => $employee,
            'resource' => $resource,
        ]);

        if ($existingFavorite) {
            $this->em->remove($existingFavorite);
            $this->em->flush();

            return false;
        }

        $favorite = new ResourceFavorite();
        $favorite->setResource($resource);
        $favorite->setEmployee($employee);

        $this->em->persist($favorite);
        $this->em->flush();

        return true;
    }
}
