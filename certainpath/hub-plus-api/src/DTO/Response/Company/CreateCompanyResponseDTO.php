<?php

namespace App\DTO\Response\Company;

class CreateCompanyResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $companyName,
        public ?string $websiteUrl,
        public ?string $uuid,
        public ?string $salesforceId = null,
        public ?string $intacctId = null,
        public ?string $companyEmail = null,
    ) {
    }
}
