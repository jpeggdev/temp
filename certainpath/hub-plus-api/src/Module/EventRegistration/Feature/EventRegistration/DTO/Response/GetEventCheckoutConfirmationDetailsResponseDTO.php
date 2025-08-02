<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Response;

/**
 * Data shape for the Confirmation Page.
 */
final class GetEventCheckoutConfirmationDetailsResponseDTO
{
    public function __construct(
        public ?string $confirmationNumber,
        public ?string $finalizedAt,
        public ?string $amount,
        public ?string $contactName,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public ?string $eventName,
        public ?string $eventSessionName,
        public ?string $startDate,
        public ?string $endDate,
        public bool $isVirtualOnly = false,
        public ?string $timezoneIdentifier = null,
        public ?string $timezoneShortName = null,
        public ?int $venueId = null,
        public ?string $venueName = null,
        public ?string $venueDescription = null,
        public ?string $venueAddress = null,
        public ?string $venueAddress2 = null,
        public ?string $venueCity = null,
        public ?string $venueState = null,
        public ?string $venuePostalCode = null,
        public ?string $venueCountry = null,
    ) {
    }
}
