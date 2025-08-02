<?php

declare(strict_types=1);

namespace App\DTO\Response\Company;

use App\Entity\Company;

readonly class EditCompanyResponseDTO
{
    public string $companyName;
    public ?string $salesforceId;
    public ?string $intacctId;
    public ?string $companyEmail;
    public bool $marketingEnabled;
    public ?int $fieldServiceSoftwareId;
    public ?string $fieldServiceSoftwareName;
    public ?string $websiteUrl;

    public function __construct(
        string $companyName,
        ?string $salesforceId,
        ?string $intacctId,
        ?string $companyEmail,
        bool $marketingEnabled,
        ?int $fieldServiceSoftwareId,
        ?string $fieldServiceSoftwareName,
        ?string $websiteUrl,
    ) {
        $this->companyName = $companyName;
        $this->salesforceId = $salesforceId;
        $this->intacctId = $intacctId;
        $this->companyEmail = $companyEmail;
        $this->marketingEnabled = $marketingEnabled;
        $this->fieldServiceSoftwareId = $fieldServiceSoftwareId;
        $this->fieldServiceSoftwareName = $fieldServiceSoftwareName;
        $this->websiteUrl = $websiteUrl;
    }

    public static function fromEntity(Company $company): self
    {
        $fieldServiceSoftware = $company->getFieldServiceSoftware();

        return new self(
            $company->getCompanyName(),
            $company->getSalesforceId(),
            $company->getIntacctId(),
            $company->getCompanyEmail(),
            $company->isMarketingEnabled(),
            $fieldServiceSoftware?->getId(),
            $fieldServiceSoftware?->getName(),
            $company->getWebsiteUrl()
        );
    }
}
