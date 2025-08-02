<?php

declare(strict_types=1);

namespace App\DTO\Request\Company;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateCompanyDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Company name should not be blank.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Company name cannot be longer than {{ limit }} characters.'
        )]
        public string $companyName,
        #[Assert\Url(
            message: "The website URL '{{ value }}' is not a valid URL."
        )]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Website URL cannot be longer than {{ limit }} characters.'
        )]
        public ?string $websiteUrl = null,
        #[Assert\Length(
            max: 255,
            maxMessage: 'Salesforce ID cannot be longer than {{ limit }} characters.'
        )]
        public ?string $salesforceId = null,
        #[Assert\NotBlank(message: 'Intacct ID Should not be blank.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Intacct ID cannot be longer than {{ limit }} characters.'
        )]
        public ?string $intacctId = null,
        #[Assert\Email(
            message: "The company email '{{ value }}' is not a valid email."
        )]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Company email cannot be longer than {{ limit }} characters.'
        )]
        public ?string $companyEmail = null,
    ) {
    }
}
