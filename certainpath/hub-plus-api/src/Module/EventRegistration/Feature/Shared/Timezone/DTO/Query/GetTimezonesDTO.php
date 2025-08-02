<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\Shared\Timezone\DTO\Query;

use Symfony\Component\Validator\Constraints as Assert;

class GetTimezonesDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'The searchTerm must be a valid integer.')]
        public ?string $searchTerm = null,
    ) {
    }
}
