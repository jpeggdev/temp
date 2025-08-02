<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class RestrictedAddressQueryDTO
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public ?string $externalId = null,
        #[Assert\Length(max: 255)]
        public ?string $address1 = null,
        #[Assert\Length(max: 255)]
        public ?string $address2 = null,
        #[Assert\Length(max: 255)]
        public ?string $city = null,
        #[Assert\Length(max: 2)]
        public ?string $stateCode = null,
        #[Assert\Length(max: 255)]
        public ?string $postalCode = null,
        #[Assert\Length(max: 2)]
        public ?string $countryCode = null,
        #[Assert\Choice(choices: ['true', 'false'])]
        public ?string $isBusiness = null,
        #[Assert\Choice(choices: ['true', 'false'])]
        public ?string $isVacant = null,
        #[Assert\Choice(choices: ['true', 'false'])]
        public ?string $isVerified = null,
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $sortOrder = 'DESC',
        public string $sortBy = 'id',
        #[Assert\Positive]
        public int $page = 1,
        #[Assert\Positive]
        public int $perPage = 10,
    ) {
    }
}
