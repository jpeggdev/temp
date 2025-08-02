<?php

declare(strict_types=1);

namespace App\DTO\Request\Resource;

use Symfony\Component\Validator\Constraints as Assert;

class SetFeaturedResourceRequestDTO
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Type('bool')]
        public bool $isFeatured,
    ) {
    }
}
