<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Service;

use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Response\ValidateEventDiscountCodeResponseDTO;
use App\Repository\EventDiscountRepository;

readonly class ValidateEventDiscountCodeService
{
    public function __construct(
        private EventDiscountRepository $eventDiscountRepository,
    ) {
    }

    public function checkCodeExists(
        string $code,
    ): ValidateEventDiscountCodeResponseDTO {
        $existingDiscount = $this->eventDiscountRepository->findOneByCode($code);

        if ($existingDiscount) {
            $codeExists = true;
            $message = sprintf('The event discount code "%s" already exists.', $code);
        } else {
            $codeExists = false;
            $message = sprintf('The event discount code "%s" is available.', $code);
        }

        return new ValidateEventDiscountCodeResponseDTO(
            codeExists: $codeExists,
            message: $message,
        );
    }
}
