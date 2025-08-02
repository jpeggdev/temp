<?php

declare(strict_types=1);

namespace App\DTO\Response\Company;

use App\Entity\Company;

readonly class UpdateCompanyTradesResponseDTO
{
    public array $tradeIds;

    public function __construct(array $tradeIds)
    {
        $this->tradeIds = $tradeIds;
    }

    public static function fromEntity(Company $company): self
    {
        $tradeIds = $company->getCompanyTrades()
            ->map(fn ($companyTrade) => $companyTrade->getTrade()->getId())
            ->toArray();

        return new self($tradeIds);
    }
}
