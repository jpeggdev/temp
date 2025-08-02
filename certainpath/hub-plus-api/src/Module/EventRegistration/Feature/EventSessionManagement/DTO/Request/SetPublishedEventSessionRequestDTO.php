<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class SetPublishedEventSessionRequestDTO
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Type('bool')]
        public bool $isPublished,
    ) {
    }
}
