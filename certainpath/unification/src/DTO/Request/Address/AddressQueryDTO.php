<?php

declare(strict_types=1);

namespace App\DTO\Request\Address;

use Symfony\Component\Validator\Constraints as Assert;

class AddressQueryDTO
{
    public const DEFAULT_SORT_ORDER = 'DESC';

    public function __construct(
        #[Assert\Length(max: 255)]
        public readonly ?string $externalId = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $companyIdentifier = null,
        public readonly ?int $companyId = null,
        public readonly ?int $customerId = null,
        public readonly ?int $prospectId = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $address1 = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $address2 = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $city = null,
        #[Assert\Length(max: 2)]
        public readonly ?string $stateCode = null,
        #[Assert\Length(max: 255)]
        public readonly ?string $postalCode = null,
        #[Assert\Length(max: 2)]
        public readonly ?string $countryCode = null,
        #[Assert\Choice(choices: ['true', 'false'], message: 'isDoNotMail must be true or false')]
        public ?string $isDoNotMail = null,
        #[Assert\Choice(choices: ['true', 'false'], message: 'isBusiness must be true or false')]
        public ?string $isBusiness = null,
        #[Assert\Choice(choices: ['true', 'false'], message: 'isVacant must be true or false')]
        public ?string $isVacant = null,
        #[Assert\Choice(choices: ['true', 'false'], message: 'isVerified must be true or false')]
        public ?string $isVerified = null,
        #[Assert\Choice(choices: ['ASC', 'DESC'])]
        public string $sortOrder = self::DEFAULT_SORT_ORDER,
        #[Assert\Choice(choices: ['id', 'externalId', 'createdAt'], message: 'Invalid sort field')]
        public string $sortBy = 'id',
    ) {
    }
}
