<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\EventCheckout;

final class InvoiceNotesGenerator
{
    /**
     * Generate the full notes, including session/attendee info
     * and (optionally) payment details if a charge response is provided.
     *
     * @throws \Exception
     */
    public function generateNotes(
        EventCheckout $eventCheckout,
        Company $company,
        ?AuthNetChargeResponseDTO $chargeResponse = null,
    ): string {
        $session = $eventCheckout->getEventSession();
        $event = $session ? $session->getEvent() : null;
        $nonWaitlistAttendees = array_filter(
            $eventCheckout->getEventCheckoutAttendees()->toArray(),
            fn ($attendee) => !$attendee->isWaitlist()
        );
        $attendeeCount = count($nonWaitlistAttendees);

        $timezone = $session?->getTimezone();
        $tzIdentifier = $timezone?->getIdentifier();

        $originalStart = $session?->getStartDate();
        $originalEnd = $session?->getEndDate();

        $startDateInTz = null;
        $endDateInTz = null;

        if ($tzIdentifier && $originalStart) {
            $startDateInTz = new \DateTime($originalStart->format('Y-m-d H:i:s'));
            $startDateInTz->setTimezone(new \DateTimeZone($tzIdentifier));
        } elseif ($originalStart) {
            $startDateInTz = new \DateTime($originalStart->format('Y-m-d H:i:s'));
        }

        if ($tzIdentifier && $originalEnd) {
            $endDateInTz = new \DateTime($originalEnd->format('Y-m-d H:i:s'));
            $endDateInTz->setTimezone(new \DateTimeZone($tzIdentifier));
        } elseif ($originalEnd) {
            $endDateInTz = new \DateTime($originalEnd->format('Y-m-d H:i:s'));
        }

        $notes = sprintf(
            "Invoice for %s\n",
            $event ? $event->getEventName() : 'Event Registration'
        );
        $notes .= sprintf("Company: %s\n", $company->getCompanyName());
        $notes .= sprintf("Registrants: %d\n", $attendeeCount);

        if ($session) {
            if ($startDateInTz && $endDateInTz) {
                $notes .= sprintf(
                    "Session: %s - %s %s\n",
                    $startDateInTz->format('m/d/Y g:i A'),
                    $endDateInTz->format('m/d/Y g:i A'),
                    $timezone?->getShortName() ?? ''
                );
            } elseif ($startDateInTz) {
                $notes .= sprintf(
                    "Session Date: %s %s\n",
                    $startDateInTz->format('m/d/Y g:i A'),
                    $timezone?->getShortName() ?? ''
                );
            }

            if ($session->getName()) {
                $notes .= sprintf("Session: %s\n", $session->getName());
            }

            $venue = $session->getVenue();
            if ($venue) {
                $notes .= sprintf("Venue: %s\n", $venue->getName() ?? 'N/A');
            }

            if ($session->isVirtualOnly()) {
                $notes .= "Session Type: Virtual\n";
            }
        }

        $notes .= 'Invoice auto-generated from event registration system.';

        if ($chargeResponse) {
            $notes .= "\n\nPayment Details:\n";
            $notes .= 'Transaction ID: '.($chargeResponse->transactionId ?: 'N/A')."\n";
            $notes .= 'Payment Profile: '.($chargeResponse->paymentProfileId ?: 'N/A')."\n";
            $notes .= 'Last4: '.($chargeResponse->accountLast4 ?: 'N/A')."\n";

            if (!empty($chargeResponse->error)) {
                $notes .= 'Payment Error: '.$chargeResponse->error."\n";
            }
        }

        return trim($notes);
    }
}
