<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Response;

use App\Entity\Company;
use App\Entity\EventVoucher;

class GetEventVoucherResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $companyName,
        public string $companyIdentifier,
        public int $totalSeats,
        public int $availableSeats,
        public string $usage,
        public bool $isActive,
        public array $company,
        public ?string $description,
        public ?\DateTimeInterface $startDate,
        public ?\DateTimeInterface $endDate,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(
        EventVoucher $eventSessionVoucher,
        string $eventVoucherUsage,
        int $availableSeats,
    ): self {
        $companyData = self::prepareCompanyData($eventSessionVoucher->getCompany());

        return new self(
            id: $eventSessionVoucher->getId(),
            name: $eventSessionVoucher->getName(),
            companyName: $eventSessionVoucher->getCompany()?->getCompanyName() ?? '',
            companyIdentifier: $eventSessionVoucher->getCompany()?->getIntacctId() ?? '',
            totalSeats: $eventSessionVoucher->getTotalSeats(),
            availableSeats: $availableSeats,
            usage: $eventVoucherUsage,
            isActive: $eventSessionVoucher->isActive(),
            company: $companyData,
            description: $eventSessionVoucher->getDescription(),
            startDate: $eventSessionVoucher->getStartDate(),
            endDate: $eventSessionVoucher->getEndDate(),
            createdAt: $eventSessionVoucher->getCreatedAt(),
            updatedAt: $eventSessionVoucher->getUpdatedAt(),
        );
    }

    private static function prepareCompanyData(Company $company): array
    {
        return [
            'id' => $company->getId(),
            'name' => $company->getCompanyName(),
            'companyIdentifier' => $company->getIntacctId(),
        ];
    }
}
