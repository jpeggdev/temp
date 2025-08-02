<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateUpdateEventVenueDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        #[Assert\Type(type: 'string', message: 'The name field must be a string.')]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Type(type: 'string', message: 'The address field must be a string.')]
        public string $address,
        #[Assert\NotBlank]
        #[Assert\Type(type: 'string', message: 'The city field must be a string.')]
        public string $city,
        #[Assert\NotBlank]
        #[Assert\Type(type: 'string', message: 'The state field must be a string.')]
        public string $state,
        #[Assert\NotBlank]
        #[Assert\Type(type: 'string', message: 'The postalCode field must be a string.')]
        public string $postalCode,
        #[Assert\NotBlank]
        #[Assert\Type(type: 'string', message: 'The country field must be a string.')]
        public string $country,
        #[Assert\Type(type: 'string', message: 'The description field must be a string.')]
        public ?string $description = null,
        #[Assert\Type(type: 'string', message: 'The address2 field must be a string.')]
        public ?string $address2 = '',
    ) {
    }
}
