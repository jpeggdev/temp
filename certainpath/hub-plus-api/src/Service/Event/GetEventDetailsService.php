<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\LoggedInUserDTO;
use App\DTO\Response\Event\GetEventDetailsResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventEmployeeRoleMapping;
use App\Entity\EventFile;
use App\Entity\EventTagMapping;
use App\Entity\EventTradeMapping;
use App\Entity\File;
use App\Repository\EventCheckoutAttendeeRepository;
use App\Repository\EventCheckoutRepository;
use App\Repository\EventEnrollmentRepository;
use App\Repository\EventFavoriteRepository;
use App\Repository\EventSession\EventSessionRepository;
use App\Service\AmazonS3Service;

final readonly class GetEventDetailsService
{
    public function __construct(
        private EventFavoriteRepository $eventFavoriteRepository,
        private EventCheckoutAttendeeRepository $eventCheckoutAttendeeRepository,
        private EventEnrollmentRepository $eventEnrollmentRepository,
        private EventSessionRepository $eventSessionRepository,
        private EventCheckoutRepository $eventCheckoutRepository,
        private AmazonS3Service $amazonS3Service,  // Add this
    ) {
    }

    public function getEventDetails(Event $event, LoggedInUserDTO $loggedInUserDTO): GetEventDetailsResponseDTO
    {
        $employee = $loggedInUserDTO->getActiveEmployee();
        $company = $loggedInUserDTO->getActiveCompany();
        $isFavorited = $this->isEventFavoritedByEmployee($event, $employee);
        $createdAt = $event->getCreatedAt()->format(\DateTimeInterface::ATOM);
        $updatedAt = $event->getUpdatedAt()->format(\DateTimeInterface::ATOM);
        $tags = $this->extractTags($event);
        $trades = $this->extractTrades($event);
        $roles = $this->extractRoles($event);
        $sessions = $this->extractSessions($event, $employee, $company);
        $files = $this->extractFiles($event);

        // Get thumbnail with presigned URL
        $thumbnailFile = $event->getThumbnail();
        $thumbnailUrl = $this->getPresignedUrlForFile($thumbnailFile);

        return new GetEventDetailsResponseDTO(
            id: $event->getId(),
            uuid: $event->getUuid(),
            eventCode: $event->getEventCode(),
            eventName: $event->getEventName(),
            eventDescription: $event->getEventDescription(),
            eventPrice: $event->getEventPrice(),
            isPublished: (bool) $event->getIsPublished(),
            eventTypeName: $event->getEventTypeName(),
            eventCategoryName: $event->getEventCategoryName(),
            thumbnailUrl: $thumbnailUrl,
            viewCount: $event->getViewCount() ?? 0,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            tags: array_values($tags),
            trades: array_values($trades),
            roles: array_values($roles),
            sessions: $sessions,
            files: $files,
            isFavorited: $isFavorited,
            isVoucherEligible: (bool) $event->isVoucherEligible(),
        );
    }

    private function getPresignedUrlForFile(?File $file): ?string
    {
        if (!$file || !$file->getBucketName() || !$file->getObjectKey()) {
            return null;
        }

        try {
            return $this->amazonS3Service->generatePresignedUrl(
                $file->getBucketName(),
                $file->getObjectKey()
            );
        } catch (\Exception $e) {
            // Log error if needed
            return null;
        }
    }

    private function isEventFavoritedByEmployee(Event $event, Employee $employee): bool
    {
        $favorite = $this->eventFavoriteRepository->findOneBy([
            'event' => $event,
            'employee' => $employee,
        ]);

        return null !== $favorite;
    }

    /**
     * @return array<int, array{id:int, name:string}>
     */
    private function extractTags(Event $event): array
    {
        $mapped = $event->getEventTagMappings()
            ->map(function (EventTagMapping $m) {
                $tag = $m->getEventTag();
                if (!$tag) {
                    return null;
                }

                return [
                    'id' => $tag->getId(),
                    'name' => $tag->getName(),
                ];
            })
            ->filter(fn ($item) => null !== $item);

        return $mapped->toArray();
    }

    /**
     * @return array<int, array{id:int, name:string}>
     */
    private function extractTrades(Event $event): array
    {
        $mapped = $event->getEventTradeMappings()
            ->map(function (EventTradeMapping $m) {
                $trade = $m->getTrade();
                if (!$trade) {
                    return null;
                }

                return [
                    'id' => $trade->getId(),
                    'name' => $trade->getName(),
                ];
            })
            ->filter(fn ($item) => null !== $item);

        return $mapped->toArray();
    }

    /**
     * @return array<int, array{id:int, name:string}>
     */
    private function extractRoles(Event $event): array
    {
        $mapped = $event->getEventEmployeeRoleMappings()
            ->map(function (EventEmployeeRoleMapping $m) {
                $role = $m->getEmployeeRole();
                if (!$role) {
                    return null;
                }

                return [
                    'id' => $role->getId(),
                    'name' => $role->getName(),
                ];
            })
            ->filter(fn ($item) => null !== $item);

        return $mapped->toArray();
    }

    private function extractSessions(Event $event, Employee $employee, Company $company): array
    {
        $nowUtc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $eventSessions = $this->eventSessionRepository->findFuturePublishedSessionsForEvent($event, $nowUtc);

        $result = [];
        foreach ($eventSessions as $session) {
            // 1) Total in-progress seats (not completed, not expired)
            $occupiedAttendeeSeats = $this->eventCheckoutAttendeeRepository
                ->countActiveAttendeesForSession($session);

            // 2) Seats specifically being held by this user
            $occupiedAttendeeSeatsByCurrentUser = $this->eventCheckoutAttendeeRepository
                ->countActiveAttendeesForSessionByEmployee($session, $employee, $company);

            // 3) Actual enrollments (completed checkouts)
            $occupiedEnrolledSeats = $this->eventEnrollmentRepository
                ->countEnrollmentsForSession($session);

            // Combine to get total seats (real enrollments + in-progress reservations)
            $occupiedSeats = $occupiedAttendeeSeats + $occupiedEnrolledSeats;
            $availableSeats = max(0, $session->getMaxEnrollments() - $occupiedSeats);

            // 4) Time left for this user's earliest reservation
            $checkout = $this->eventCheckoutRepository
                ->findEarliestActiveCheckoutForUserAndSession($session, $employee, $company);

            $timeLeftForCurrentUser = null;
            if (null !== $checkout) {
                $expirationTimestamp = $checkout->getReservationExpiresAt()->getTimestamp();
                $nowTimestamp = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->getTimestamp();
                $timeLeftForCurrentUser = max(0, $expirationTimestamp - $nowTimestamp);
            }

            // 5) Venue data, etc. (existing code)
            $venueEntity = $session->getVenue();
            $venue = null;
            if (null !== $venueEntity) {
                $venue = [
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

            // Place all data in the array
            $result[] = [
                'id' => $session->getId(),
                'uuid' => $session->getUuid(),
                'name' => $session->getName(),
                'isPublished' => $session->getIsPublished(),
                'startDate' => $session->getStartDate()->format(\DateTimeInterface::ATOM),
                'endDate' => $session->getEndDate()->format(\DateTimeInterface::ATOM),
                'maxEnrollments' => $session->getMaxEnrollments(),
                'availableSeats' => $availableSeats,
                'virtualLink' => $session->getVirtualLink(),
                'notes' => $session->getNotes(),
                'isVirtualOnly' => (bool) $session->isVirtualOnly(),
                'timezoneIdentifier' => $session->getTimezone()?->getIdentifier(),
                'timezoneShortName' => $session->getTimezone()?->getShortName(),
                'venue' => $venue,
                'occupiedAttendeeSeatsByCurrentUser' => $occupiedAttendeeSeatsByCurrentUser,
                'timeLeftForCurrentUser' => $timeLeftForCurrentUser,
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{
     *     id: int|null,
     *     uuid: string|null,
     *     originalFileName: ?string,
     *     fileUrl: ?string
     * }>
     */
    private function extractFiles(Event $event): array
    {
        return $event->getEventFiles()->map(
            function (EventFile $eventFile) {
                $file = $eventFile->getFile();

                return [
                    'id' => $file->getId(),
                    'uuid' => $file->getUuid(),
                    'originalFileName' => $file->getOriginalFileName(),
                    'fileUrl' => $this->getPresignedUrlForFile($file),
                ];
            }
        )->toArray();
    }
}
