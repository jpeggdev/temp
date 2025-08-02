<?php

declare(strict_types=1);

namespace App\DTO\Response\Company;

use App\Entity\Company;

class GetMyCompanyResponseDTO
{
    public function __construct(
        public string $uuid,
        public string $companyName,
    ) {
    }

    public static function fromEntity(Company $company): self
    {
        return new self(
            $company->getUuid(),
            $company->getCompanyName()
        );
    }
}
