<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\DTO\LoggedInUserDTO;
use App\Entity\Company;
use App\Entity\Event;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\GetEventCheckoutSessionDetailsResponseDTO;
use App\Repository\CreditMemoLineItemRepository;
use App\Repository\EventDiscountRepository;
use App\Repository\EventEnrollmentRepository;
use App\Repository\EventVoucherRepository;
use App\Repository\InvoiceLineItemRepository;

final readonly class GetEventCheckoutSessionDetailsService
{
    public function __construct(
        private EventVoucherRepository $eventVoucherRepository,
        private CreditMemoLineItemRepository $creditMemoLineItemRepository,
        private EventDiscountRepository $eventDiscountRepository,
        private InvoiceLineItemRepository $invoiceLineItemRepository,
        private EventSessionSeatCalculatorService $eventSessionSeatCalculatorService,
        private EventEnrollmentRepository $eventEnrollmentRepository,
    ) {
    }

    public function getDetails(
        EventCheckout $eventCheckoutSession,
        Company $company,
        LoggedInUserDTO $loggedInUserDTO,
    ): GetEventCheckoutSessionDetailsResponseDTO {
        $eventSession = $eventCheckoutSession->getEventSession();
        $event = $eventSession?->getEvent();

        $eventName = $event?->getEventName();
        $eventUuid = $event?->getUuid();
        $eventPrice = $event?->getEventPrice() ?? 0.0;
        $eventSessionName = $eventSession?->getName();
        $maxEnrollments = $eventSession?->getMaxEnrollments() ?? 0;
        $notes = $eventSession?->getNotes();
        $numberOfEnrollments = $eventCheckoutSession->getEventCheckoutAttendees()->count();
        $startDate = $eventSession?->getStartDate()?->format(\DateTimeInterface::ATOM);
        $endDate = $eventSession?->getEndDate()?->format(\DateTimeInterface::ATOM);

        $sessionId = $eventCheckoutSession->getId();
        $uuid = $eventCheckoutSession->getUuid();
        $status = $eventCheckoutSession->getStatus()?->value;
        $reservationExpiresAt = $eventCheckoutSession->getReservationExpiresAt()?->format(\DateTimeInterface::ATOM);
        $createdById = $eventCheckoutSession->getCreatedBy()?->getId();
        $contactName = $eventCheckoutSession->getContactName();
        $contactEmail = $eventCheckoutSession->getContactEmail();
        $contactPhone = $eventCheckoutSession->getContactPhone();
        $groupNotes = $eventCheckoutSession->getGroupNotes();
        $createdAt = $eventCheckoutSession->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $updatedAt = $eventCheckoutSession->getUpdatedAt()->format(\DateTimeInterface::ATOM);

        $attendees = $this->buildAttendees($eventCheckoutSession, $loggedInUserDTO);

        $now = new \DateTimeImmutable();
        $companyAvailableVoucherSeats = 0;
        if ($event && $event->isVoucherEligible()) {
            $companyAvailableVoucherSeats = $this->buildCompanyAvailableVoucherSeats($company, $now);
        }

        $checkoutSubtotal = $eventPrice * $numberOfEnrollments;
        $discounts = $this->buildDiscounts($checkoutSubtotal, $event, $now);

        $occupiedAttendeeSeatsByCurrentUser = 0;
        $availableSeats = 0;

        if (null !== $eventSession) {
            $seatData = $this->eventSessionSeatCalculatorService->calculate(
                eventSession: $eventSession,
                company: $company,
                employee: $loggedInUserDTO->getActiveEmployee()
            );
            $occupiedAttendeeSeatsByCurrentUser = $seatData['occupiedAttendeeSeatsByCurrentUser'];
            $availableSeats = $seatData['availableSeats'];
        }

        $venueData = null;
        if ($eventSession && $eventSession->getVenue()) {
            $venueEntity = $eventSession->getVenue();
            $venueData = [
                'id' => $venueEntity->getId(),
                'name' => $venueEntity->getName(),
                'description' => $venueEntity->getDescription(),
                'address' => $venueEntity->getAddress(),
                'address2' => $venueEntity->getAddress2(),
                'city' => $venueEntity->getCity(),
                'state' => $venueEntity->getState(),
                'postalCode' => $venueEntity->getPostalCode(),
                'country' => $venueEntity->getCountry(),
            ];
        }

        $timezoneIdentifier = $eventSession?->getTimezone()?->getIdentifier();
        $timezoneShortName = $eventSession?->getTimezone()?->getShortName();
        $isVirtualOnly = (bool) $eventSession?->isVirtualOnly();

        return new GetEventCheckoutSessionDetailsResponseDTO(
            id: $sessionId,
            eventUuid: $eventUuid,
            eventSessionUuid: $eventSession?->getUuid(),
            uuid: $uuid,
            status: $status,
            reservationExpiresAt: $reservationExpiresAt,
            createdById: $createdById ?? 0,
            eventSessionId: $eventSession?->getId(),
            contactName: $contactName,
            contactEmail: $contactEmail,
            contactPhone: $contactPhone,
            groupNotes: $groupNotes,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            attendees: $attendees,
            eventName: $eventName,
            eventPrice: $eventPrice,
            eventSessionName: $eventSessionName,
            maxEnrollments: $maxEnrollments,
            availableSeats: $availableSeats,
            notes: $notes,
            startDate: $startDate,
            endDate: $endDate,
            companyAvailableVoucherSeats: $companyAvailableVoucherSeats,
            discounts: $discounts,
            venue: $venueData,
            timezoneIdentifier: $timezoneIdentifier,
            timezoneShortName: $timezoneShortName,
            isVirtualOnly: $isVirtualOnly,
            occupiedAttendeeSeatsByCurrentUser: $occupiedAttendeeSeatsByCurrentUser ?? 0,
        );
    }

    /**
     * Builds a list of attendees for the checkout session.
     * If no attendees exist in the checkout, we attempt to add a default attendee
     * based on the logged-in user, provided:
     *   - they are not already enrolled in the session,
     *   - and their employee record's company ID matches the active company ID.
     */
    private function buildAttendees(EventCheckout $eventCheckoutSession, LoggedInUserDTO $loggedInUserDTO): array
    {
        $attendees = [];
        foreach ($eventCheckoutSession->getEventCheckoutAttendees() as $attendee) {
            $attendees[] = [
                'id' => $attendee->getId(),
                'email' => $attendee->getEmail(),
                'firstName' => $attendee->getFirstName(),
                'lastName' => $attendee->getLastName(),
                'specialRequests' => $attendee->getSpecialRequests(),
                'isSelected' => $attendee->isSelected(),
                'isWaitlist' => $attendee->isWaitlist(),
            ];
        }

        $eventSession = $eventCheckoutSession->getEventSession();
        if (0 === \count($attendees) && null !== $eventSession) {
            $user = $loggedInUserDTO->getUser();
            $employee = $loggedInUserDTO->getActiveEmployee();
            $activeCompany = $loggedInUserDTO->getActiveCompany();

            // Check if user is already enrolled in the event session
            $foundEnrollment = $this->eventEnrollmentRepository->findOneByEventSessionAndEmail(
                $eventSession->getId(),
                $user->getEmail()
            );

            // Only add the default attendee if they're not already enrolled
            // and the active employee's company matches the active company
            if (
                null === $foundEnrollment
                && $employee->getCompany()?->getId() === $activeCompany->getId()
            ) {
                $attendees[] = [
                    'id' => -1,
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'specialRequests' => null,
                    'isSelected' => true,
                    'isWaitlist' => false,
                ];
            }
        }

        return $attendees;
    }

    private function buildCompanyAvailableVoucherSeats(Company $company, \DateTimeImmutable $now): int
    {
        $companyVouchers = $this->eventVoucherRepository->findBy(['company' => $company]);
        $companyTotalSeats = 0;
        foreach ($companyVouchers as $voucher) {
            if (
                $voucher->isActive()
                && (null === $voucher->getStartDate() || $voucher->getStartDate() <= $now)
                && (null === $voucher->getEndDate() || $voucher->getEndDate() >= $now)
            ) {
                $companyTotalSeats += $voucher->getTotalSeats() ?? 0;
            }
        }
        $companyUsedSeats = $this->creditMemoLineItemRepository->countVoucherLineItemsForCompany($company);

        return \max($companyTotalSeats - $companyUsedSeats, 0);
    }

    private function buildDiscounts(float $checkoutSubtotal, ?Event $event, \DateTimeImmutable $now): array
    {
        $discounts = [];
        $allDiscountEntities = $this->eventDiscountRepository->findAll();

        foreach ($allDiscountEntities as $discount) {
            if (
                !$discount->isActive()
                || (null !== $discount->getStartDate() && $discount->getStartDate() > $now)
                || (null !== $discount->getEndDate() && $discount->getEndDate() < $now)
            ) {
                continue;
            }
            $minPurchase = $discount->getMinimumPurchaseAmount();
            if (null !== $minPurchase && $checkoutSubtotal < (float) $minPurchase) {
                continue;
            }
            if (null !== $discount->getMaximumUses()) {
                $usesCount = $this->invoiceLineItemRepository
                    ->countInvoiceLineItemsByDiscountCode($discount->getCode());
                if ($usesCount >= $discount->getMaximumUses()) {
                    continue;
                }
            }
            $discountEventMappings = $discount->getEventEventDiscounts();
            if (\count($discountEventMappings) > 0 && null !== $event) {
                $eventMatches = false;
                foreach ($discountEventMappings as $mapping) {
                    $mappedEvent = $mapping->getEvent();
                    if (null !== $mappedEvent && $mappedEvent->getId() === $event->getId()) {
                        $eventMatches = true;
                        break;
                    }
                }
                if (!$eventMatches) {
                    continue;
                }
            }

            $discounts[] = [
                'id' => $discount->getId(),
                'code' => $discount->getCode(),
                'discountType' => $discount->getDiscountType()?->getName(),
                'discountValue' => $discount->getDiscountValue(),
            ];
        }

        return $discounts;
    }
}
