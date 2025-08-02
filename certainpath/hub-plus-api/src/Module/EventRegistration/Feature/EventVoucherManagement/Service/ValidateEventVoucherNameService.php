<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Service;

use App\DTO\Response\Event\ValidateEventNameResponseDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Response\ValidateEventVoucherNameResponse;
use App\Repository\EventVoucherRepository;

readonly class ValidateEventVoucherNameService
{
    public function __construct(
        private EventVoucherRepository $eventVoucherRepository,
    ) {
    }

    public function checkNameExists(
        string $code,
    ): ValidateEventVoucherNameResponse {
        $existingEvent = $this->eventVoucherRepository->findOneByCode($code);

        if ($existingEvent) {
            $nameExists = true;
            $message = sprintf('The voucher name "%s" already exists.', $code);
        } else {
            $nameExists = false;
            $message = sprintf('The voucher name "%s" is available.', $code);
        }

        return new ValidateEventVoucherNameResponse(
            nameExists: $nameExists,
            message: $message,
        );
    }
}
