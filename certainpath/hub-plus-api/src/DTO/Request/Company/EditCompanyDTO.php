<?php

declare(strict_types=1);

namespace App\DTO\Request\Company;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EditCompanyDTO
{
    public function __construct(
        #[Assert\NotNull]
        public bool $marketingEnabled,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $companyName,
        #[Assert\Length(max: 255)]
        public ?string $salesforceId = null,
        #[Assert\NotBlank(message: 'Intacct ID Should not be blank.')]
        #[Assert\Length(max: 255)]
        public ?string $intacctId = null,
        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public ?string $companyEmail = null,
        #[Assert\Url]
        public ?string $websiteUrl = null,
    ) {
    }
}
