<?php

namespace App\DTO\Request\Location;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateLocationDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The name field cannot be empty')]
        #[Assert\Length(max: 255, maxMessage: 'The name field cannot be longer than {{ limit }} characters.')]
        public string $name,

        #[Assert\NotBlank(message: 'The company identifier field cannot be empty')]
        #[Assert\Length(max: 255, maxMessage: 'The company identifier field cannot be longer than {{ limit }} characters.')]
        public string $companyIdentifier,

        #[Assert\Type(type: 'string', message: 'The description field must be a string.')]
        public ?string $description = null,

        #[Assert\NotBlank(message: 'The postalCodes field cannot be empty.')]
        #[Assert\Type(type: 'array', message: 'The postalCodes field must be an array.')]
        #[Assert\All([
            new Assert\Type(['type' => 'string', 'message' => 'Each postal code must be a string.']),
            new Assert\NotBlank(['message' => 'Postal codes cannot be blank.']),
        ])]
        public array $postalCodes = [],
    ) {
    }
}
