<?php

namespace App\DTO\Query\Invoice;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

readonly class DailySalesQueryDTO
{
    public function __construct(
        #[Assert\Type('string')]
        public string $companyIdentifier,
        #[Assert\Type(DateTimeInterface::class)]
        public DateTimeInterface $startDate,
        #[Assert\Type(DateTimeInterface::class)]
        public DateTimeInterface $endDate,
        #[Assert\Type('string')]
        public string $orderBy = 'i.invoicedAt',
        #[Assert\Type('string')]
        public string $sortOrder = 'ASC',
    ) {
    }
}
