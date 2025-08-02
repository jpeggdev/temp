<?php

namespace App\DTO\Response;

class GetMyCompanyProfileResponseDTO
{
    public string $companyName;
    public ?string $companyEmail;
    public ?string $websiteUrl;
    public ?string $addressLine1;
    public ?string $addressLine2;
    public ?string $city;
    public ?string $state;
    public ?string $country;
    public ?string $zipCode;
    public bool $isMailingAddressSame;
    public ?string $mailingAddressLine1;
    public ?string $mailingAddressLine2;
    public ?string $mailingState;
    public ?string $mailingCountry;
    public ?string $mailingZipCode;
    public string $uuid;

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
        string $uuid,
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
        $this->uuid = $uuid;
    }
}
