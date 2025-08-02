<?php

declare(strict_types=1);

namespace App\DTO\Query\Location;

use Symfony\Component\Validator\Constraints as Assert;

class LocationsQueryDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'The searchTerm field must be a string.')]
        public ?string $searchTerm = null,

        #[Assert\Type(type: 'bool', message: 'The isActive field must be a boolean.')]
        public bool $isActive = true,

        #[Assert\NotBlank(message: 'The company identifier field cannot be empty')]
        #[Assert\Length(max: 255, maxMessage: 'The company identifier field cannot be longer than {{ limit }} characters.')]
        public ?string $companyIdentifier = null,
    ) {
    }
}
