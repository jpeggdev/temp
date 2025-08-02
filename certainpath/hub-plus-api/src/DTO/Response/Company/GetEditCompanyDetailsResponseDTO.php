<?php

namespace App\DTO\Response\Company;

class GetEditCompanyDetailsResponseDTO
{
    public string $companyName;
    public ?string $salesforceId;
    public ?string $intacctId;
    public bool $marketingEnabled;
    public ?string $companyEmail;
    public ?int $fieldServiceSoftwareId;
    public ?string $fieldServiceSoftwareName;
    public ?string $websiteUrl;
    public array $fieldServiceSoftwareList;
    public array $tradeList;
    public array $companyTradeIds;

    public function __construct(
        string $companyName,
        ?string $salesforceId,
        ?string $intacctId,
        bool $marketingEnabled,
        ?string $companyEmail,
        ?int $fieldServiceSoftwareId,
        ?string $fieldServiceSoftwareName,
        ?string $websiteUrl,
        array $fieldServiceSoftwareList,
        array $tradeList,
        array $companyTradeIds,
    ) {
        $this->companyName = $companyName;
        $this->salesforceId = $salesforceId;
        $this->intacctId = $intacctId;
        $this->marketingEnabled = $marketingEnabled;
        $this->companyEmail = $companyEmail;
        $this->fieldServiceSoftwareId = $fieldServiceSoftwareId;
        $this->fieldServiceSoftwareName = $fieldServiceSoftwareName;
        $this->websiteUrl = $websiteUrl;
        $this->fieldServiceSoftwareList = $fieldServiceSoftwareList;
        $this->tradeList = $tradeList;
        $this->companyTradeIds = $companyTradeIds;
    }
}
