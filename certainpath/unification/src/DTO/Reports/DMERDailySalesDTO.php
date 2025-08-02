<?php

namespace App\DTO\Reports;

use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Validator\Constraints as Assert;

class DMERDailySalesDTO implements Arrayable
{
    public function __construct(
        #[Assert\Type(DateTimeInterface::class)]
        public DateTimeInterface $date,
        #[Assert\Type('string')]
        public string $totalCalls = '',
        #[Assert\Type('string')]
        public string $totalSales = '',
        #[Assert\Type('string')]
        public string $totalSalesAmount = '',
        #[Assert\Type('string')]
        public string $serviceTypeCategory = 'unknown',
    ) {
    }

    public static function createEmptyInstance(DateTimeInterface $toDateTime): DMERDailySalesDTO
    {
        return new self($toDateTime, 0, 0, 0, 'unknown');
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getServiceTypeCategory(): string
    {
        return $this->serviceTypeCategory;
    }

    public function setServiceTypeCategory(string $category): DMERDailySalesDTO
    {
        $this->serviceTypeCategory = $category;
        return $this;
    }
    public function toArray(): array
    {
        return [
            'totalCalls' => $this->totalCalls,
            'totalSales' => $this->totalSales,
            'totalSalesAmount' => $this->totalSalesAmount,
        ];
    }
}
