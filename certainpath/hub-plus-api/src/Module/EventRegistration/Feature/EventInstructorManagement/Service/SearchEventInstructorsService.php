<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Service;

use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request\SearchEventInstructorsRequestDTO;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Response\SearchEventInstructorsResponseDTO;
use App\Repository\EventInstructorRepository;

readonly class SearchEventInstructorsService
{
    public function __construct(
        private EventInstructorRepository $eventInstructorRepository,
    ) {
    }

    /**
     * @return array{
     *     instructors: SearchEventInstructorsResponseDTO[],
     *     totalCount: int
     * }
     */
    public function search(SearchEventInstructorsRequestDTO $dto): array
    {
        $instructors = $this->eventInstructorRepository->findInstructorsByQuery($dto);
        $totalCount = $this->eventInstructorRepository->countInstructorsByQuery($dto);
        $instructorDtos = SearchEventInstructorsResponseDTO::fromEntities($instructors);

        return [
            'instructors' => $instructorDtos,
            'totalCount' => $totalCount,
        ];
    }
}
