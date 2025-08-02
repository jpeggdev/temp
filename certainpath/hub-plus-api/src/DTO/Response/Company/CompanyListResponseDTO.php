<?php

declare(strict_types=1);

namespace App\DTO\Response\Company;

use App\Entity\Company;

class CompanyListResponseDTO
{
    public function __construct(
        public int $id,
        public string $companyName,
        public string $uuid,
        public ?string $salesforceId = null,
        public ?string $intacctId = null,
        public ?bool $marketingEnabled = null,
        public ?bool $isCertainPath = null,
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
    ) {
    }

    public static function fromEntity(Company $company): self
    {
        return new self(
            $company->getId(),
            $company->getCompanyName(),
            $company->getUuid(),
            $company->getSalesforceId(),
            $company->getIntacctId(),
            $company->isMarketingEnabled(),
            $company->isCertainPath(),
            $company->getCreatedAt(),
            $company->getUpdatedAt()
        );
    }
}
