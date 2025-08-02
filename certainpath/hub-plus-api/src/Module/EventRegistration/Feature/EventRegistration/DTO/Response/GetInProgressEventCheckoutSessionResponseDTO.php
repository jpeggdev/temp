<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Response;

final class GetInProgressEventCheckoutSessionResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public ?string $createdAt,
        public ?string $eventName,
        public ?string $eventSessionName,
        public ?string $startDate,
        public ?string $endDate,
    ) {
    }
}
