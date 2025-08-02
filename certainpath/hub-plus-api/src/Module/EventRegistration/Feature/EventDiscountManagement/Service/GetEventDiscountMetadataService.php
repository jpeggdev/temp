<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Service;

use App\Entity\DiscountType;
use App\Repository\DiscountTypeRepository;

readonly class GetEventDiscountMetadataService
{
    public function __construct(
        private DiscountTypeRepository $discountTypeRepository,
    ) {
    }

    public function getMetadata(): array
    {
        $eventDiscountPercentage = $this->discountTypeRepository->findOneByNameOrFail(
            DiscountType::EVENT_TYPE_PERCENTAGE
        );
        $eventDiscountFixedAmount = $this->discountTypeRepository->findOneByNameOrFail(
            DiscountType::EVENT_TYPE_FIXED_AMOUNT
        );

        return [
            'discountTypes' => [
                [
                    'id' => $eventDiscountPercentage->getId(),
                    'name' => $eventDiscountPercentage->getName(),
                    'displayName' => $eventDiscountPercentage->getDisplayName(),
                    'isDefault' => true,
                ],
                [
                    'id' => $eventDiscountFixedAmount->getId(),
                    'name' => $eventDiscountFixedAmount->getName(),
                    'displayName' => $eventDiscountFixedAmount->getDisplayName(),
                    'isDefault' => false,
                ],
            ]];
    }
}
