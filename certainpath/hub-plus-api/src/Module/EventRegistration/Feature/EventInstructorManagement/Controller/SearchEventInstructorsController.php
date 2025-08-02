<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request\SearchEventInstructorsRequestDTO;
use App\Module\EventRegistration\Feature\EventInstructorManagement\Service\SearchEventInstructorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class SearchEventInstructorsController extends ApiController
{
    public function __construct(
        private readonly SearchEventInstructorsService $searchEventInstructorsService,
    ) {
    }

    #[Route('/event-instructors', name: 'api_event_instructors_search', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] SearchEventInstructorsRequestDTO $requestDto,
        Request $request,
    ): Response {
        $data = $this->searchEventInstructorsService->search($requestDto);

        return $this->createSuccessResponse(
            $data,
            $data['totalCount']
        );
    }
}
