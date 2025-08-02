<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Response;

final class GetEventCheckoutSessionDetailsResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $eventUuid,
        public ?string $eventSessionUuid,
        public ?string $uuid,
        public ?string $status,
        public ?string $reservationExpiresAt,
        public int $createdById,
        public ?int $eventSessionId,
        public ?string $contactName,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public ?string $groupNotes,
        public ?string $createdAt,
        public ?string $updatedAt,
        /**
         * An array of attendees, each item might look like:
         * [
         *   'id' => int,
         *   'email' => string,
         *   'firstName' => string,
         *   'lastName' => string,
         *   'specialRequests' => string|null,
         * ]
         */
        public array $attendees,
        public ?string $eventName,
        public ?float $eventPrice,
        public ?string $eventSessionName,
        public ?int $maxEnrollments,
        public ?int $availableSeats,
        public ?string $notes,
        public ?string $startDate,
        public ?string $endDate,
        public int $companyAvailableVoucherSeats,
        /**
         * Array of discounts, each item might look like:
         * [
         *   'id' => int,
         *   'code' => string|null,
         *   'discountType' => string|null, // e.g. 'percentage' or 'fixed_amount'
         *   'discountValue' => string|null
         * ]
         */
        public array $discounts,
        public ?array $venue,
        public ?string $timezoneIdentifier,
        public ?string $timezoneShortName,
        public bool $isVirtualOnly,
        public ?int $occupiedAttendeeSeatsByCurrentUser,
    ) {
    }
}
