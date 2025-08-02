<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\Company;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\GetEventCheckoutConfirmationDetailsResponseDTO;

final readonly class GetEventCheckoutConfirmationDetailsService
{
    public function getDetails(
        EventCheckout $eventCheckout,
        Company $company,
    ): GetEventCheckoutConfirmationDetailsResponseDTO {
        $confirmationNumber = $eventCheckout->getConfirmationNumber();
        $finalizedAt = $eventCheckout->getFinalizedAt()?->format(\DateTimeInterface::ATOM);
        $amount = $eventCheckout->getAmount();
        $contactName = $eventCheckout->getContactName();
        $contactEmail = $eventCheckout->getContactEmail();
        $contactPhone = $eventCheckout->getContactPhone();

        $eventSession = $eventCheckout->getEventSession();
        $eventSessionName = $eventSession?->getName();
        $startDate = $eventSession?->getStartDate()?->format(\DateTimeInterface::ATOM);
        $endDate = $eventSession?->getEndDate()?->format(\DateTimeInterface::ATOM);
        $isVirtualOnly = $eventSession?->isVirtualOnly() ?? false;

        $timezoneEntity = $eventSession?->getTimezone();
        $timezoneIdentifier = $timezoneEntity?->getIdentifier();
        $timezoneShortName = $timezoneEntity?->getShortName();
        $event = $eventSession?->getEvent();
        $eventName = $event?->getEventName();

        $venueId = null;
        $venueName = null;
        $venueDescription = null;
        $venueAddress = null;
        $venueAddress2 = null;
        $venueCity = null;
        $venueState = null;
        $venuePostalCode = null;
        $venueCountry = null;

        if ($venueEntity = $eventSession?->getVenue()) {
            $venueId = $venueEntity->getId();
            $venueName = $venueEntity->getName();
            $venueDescription = $venueEntity->getDescription();
            $venueAddress = $venueEntity->getAddress();
            $venueAddress2 = $venueEntity->getAddress2();
            $venueCity = $venueEntity->getCity();
            $venueState = $venueEntity->getState();
            $venuePostalCode = $venueEntity->getPostalCode();
            $venueCountry = $venueEntity->getCountry();
        }

        return new GetEventCheckoutConfirmationDetailsResponseDTO(
            confirmationNumber: $confirmationNumber,
            finalizedAt: $finalizedAt,
            amount: $amount,
            contactName: $contactName,
            contactEmail: $contactEmail,
            contactPhone: $contactPhone,
            eventName: $eventName,
            eventSessionName: $eventSessionName,
            startDate: $startDate,
            endDate: $endDate,
            isVirtualOnly: $isVirtualOnly,
            timezoneIdentifier: $timezoneIdentifier,
            timezoneShortName: $timezoneShortName,
            venueId: $venueId,
            venueName: $venueName,
            venueDescription: $venueDescription,
            venueAddress: $venueAddress,
            venueAddress2: $venueAddress2,
            venueCity: $venueCity,
            venueState: $venueState,
            venuePostalCode: $venuePostalCode,
            venueCountry: $venueCountry
        );
    }
}
