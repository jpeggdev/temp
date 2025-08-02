<?php

namespace App\Entity;

use App\Repository\RestrictedAddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestrictedAddressRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'restricted_address_address_external_id_idx', columns: ['external_id'])]
class RestrictedAddress extends AbstractAddress
{
    use Traits\PostProcessEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
