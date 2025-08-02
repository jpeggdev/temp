<?php

namespace App\DTO\Request\Company;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateMyCompanyProfileRequestDTO
{
    #[Assert\NotBlank(message: 'Company name is required.')]
    #[Assert\Length(max: 255, maxMessage: 'Company name cannot exceed {{ limit }} characters.')]
    public string $companyName;

    #[Assert\Email(message: 'Please provide a valid email address for the company.')]
    #[Assert\Length(max: 255, maxMessage: 'Company email cannot exceed {{ limit }} characters.')]
    public ?string $companyEmail;

    #[Assert\Url(message: 'Please provide a valid URL for the company website.')]
    #[Assert\Length(max: 255, maxMessage: 'Website URL cannot exceed {{ limit }} characters.')]
    public ?string $websiteUrl;

    #[Assert\Length(max: 255, maxMessage: 'Address Line 1 cannot exceed {{ limit }} characters.')]
    public ?string $addressLine1;

    #[Assert\Length(max: 255, maxMessage: 'Address Line 2 cannot exceed {{ limit }} characters.')]
    public ?string $addressLine2;

    #[Assert\Length(max: 255, maxMessage: 'City name cannot exceed {{ limit }} characters.')]
    public ?string $city;

    #[Assert\Length(max: 255, maxMessage: 'State name cannot exceed {{ limit }} characters.')]
    public ?string $state;

    #[Assert\Length(max: 255, maxMessage: 'Country name cannot exceed {{ limit }} characters.')]
    public ?string $country;

    #[Assert\Length(max: 255, maxMessage: 'Zip Code cannot exceed {{ limit }} characters.')]
    public ?string $zipCode;

    #[Assert\Type(type: 'bool', message: 'The value of "Is Mailing Address Same" must be true or false.')]
    public bool $isMailingAddressSame;

    #[Assert\Length(max: 255, maxMessage: 'Mailing Address Line 1 cannot exceed {{ limit }} characters.')]
    public ?string $mailingAddressLine1;

    #[Assert\Length(max: 255, maxMessage: 'Mailing Address Line 2 cannot exceed {{ limit }} characters.')]
    public ?string $mailingAddressLine2;

    #[Assert\Length(max: 255, maxMessage: 'Mailing State cannot exceed {{ limit }} characters.')]
    public ?string $mailingState;

    #[Assert\Length(max: 255, maxMessage: 'Mailing Country cannot exceed {{ limit }} characters.')]
    public ?string $mailingCountry;

    #[Assert\Length(max: 255, maxMessage: 'Mailing Zip Code cannot exceed {{ limit }} characters.')]
    public ?string $mailingZipCode;

    public function __construct(
        string $companyName,
        ?string $companyEmail,
        ?string $websiteUrl,
        ?string $addressLine1,
        ?string $addressLine2,
        ?string $city,
        ?string $state,
        ?string $country,
        ?string $zipCode,
        bool $isMailingAddressSame,
        ?string $mailingAddressLine1,
        ?string $mailingAddressLine2,
        ?string $mailingState,
        ?string $mailingCountry,
        ?string $mailingZipCode,
    ) {
        $this->companyName = $companyName;
        $this->companyEmail = $companyEmail;
        $this->websiteUrl = $websiteUrl;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->zipCode = $zipCode;
        $this->isMailingAddressSame = $isMailingAddressSame;
        $this->mailingAddressLine1 = $mailingAddressLine1;
        $this->mailingAddressLine2 = $mailingAddressLine2;
        $this->mailingState = $mailingState;
        $this->mailingCountry = $mailingCountry;
        $this->mailingZipCode = $mailingZipCode;
    }
}
