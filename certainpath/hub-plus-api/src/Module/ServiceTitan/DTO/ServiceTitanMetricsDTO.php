<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\DTO;

use JsonSerializable;

class ServiceTitanMetricsDTO implements JsonSerializable
{
    public function __construct(
        private readonly int $totalMembers,
        private readonly int $totalInvoices,
        private readonly int $membersLastMonth,
        private readonly int $invoicesLastMonth,
        private readonly float $memberGrowthRate,
        private readonly float $invoiceGrowthRate,
        private readonly array $dataTypeMetrics,
        private readonly array $environmentMetrics,
    ) {
    }

    public function getTotalMembers(): int
    {
        return $this->totalMembers;
    }

    public function getTotalInvoices(): int
    {
        return $this->totalInvoices;
    }

    public function getMembersLastMonth(): int
    {
        return $this->membersLastMonth;
    }

    public function getInvoicesLastMonth(): int
    {
        return $this->invoicesLastMonth;
    }

    public function getMemberGrowthRate(): float
    {
        return $this->memberGrowthRate;
    }

    public function getInvoiceGrowthRate(): float
    {
        return $this->invoiceGrowthRate;
    }

    public function getDataTypeMetrics(): array
    {
        return $this->dataTypeMetrics;
    }

    public function getEnvironmentMetrics(): array
    {
        return $this->environmentMetrics;
    }

    public function jsonSerialize(): array
    {
        return [
            'totalMembers' => $this->totalMembers,
            'totalInvoices' => $this->totalInvoices,
            'membersLastMonth' => $this->membersLastMonth,
            'invoicesLastMonth' => $this->invoicesLastMonth,
            'memberGrowthRate' => $this->memberGrowthRate,
            'invoiceGrowthRate' => $this->invoiceGrowthRate,
            'dataTypeMetrics' => $this->dataTypeMetrics,
            'environmentMetrics' => $this->environmentMetrics,
        ];
    }
}
