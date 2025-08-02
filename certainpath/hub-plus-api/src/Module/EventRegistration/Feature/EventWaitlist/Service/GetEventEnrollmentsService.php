<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\Employee;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\EventEnrollmentsQueryDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response\EventEnrollmentItemResponseDTO;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentRepository;

readonly class GetEventEnrollmentsService
{
    public function __construct(
        private EventEnrollmentRepository $eventEnrollmentRepository,
        private EmployeeRepository $employeeRepository,
    ) {
    }

    /**
     * @return array{
     *   items: EventEnrollmentItemResponseDTO[],
     *   totalCount: int
     * }
     */
    public function getEventEnrollments(
        EventSession $eventSession,
        EventEnrollmentsQueryDTO $queryDto,
    ): array {
        $enrollmentEntities = $this->eventEnrollmentRepository
            ->findEnrollmentsForSessionByQueryDTO($eventSession, $queryDto);

        $totalCount = $this->eventEnrollmentRepository
            ->countEnrollmentsForSessionByQueryDTO($eventSession, $queryDto);

        $items = [];
        foreach ($enrollmentEntities as $enrollment) {
            $company = $enrollment->getEventCheckout()?->getCompany();
            $nonEnrolledEmps = $this->employeeRepository
                ->findAllNotEnrolledInSessionByCompany($eventSession, $company);

            $replacements = array_map(
                static function (Employee $emp) {
                    return [
                        'employeeId' => $emp->getId(),
                        'firstName' => $emp->getFirstName(),
                        'lastName' => $emp->getLastName(),
                        'workEmail' => $emp->getUser()->getEmail(),
                    ];
                },
                $nonEnrolledEmps
            );

            $items[] = EventEnrollmentItemResponseDTO::fromEntityWithReplacements(
                $enrollment,
                $replacements
            );
        }

        return [
            'items' => $items,
            'totalCount' => $totalCount,
        ];
    }
}
