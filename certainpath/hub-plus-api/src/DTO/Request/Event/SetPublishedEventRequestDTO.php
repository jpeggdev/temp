<?php

declare(strict_types=1);

namespace App\DTO\Request\Event;

use Symfony\Component\Validator\Constraints as Assert;

class SetPublishedEventRequestDTO
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Type('bool')]
        public bool $isPublished,
    ) {
    }
}
