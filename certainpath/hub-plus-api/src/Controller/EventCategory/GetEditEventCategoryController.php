<?php

declare(strict_types=1);

namespace App\Controller\EventCategory;

use App\Controller\ApiController;
use App\Entity\EventCategory;
use App\Security\Voter\EventCategorySecurityVoter;
use App\Service\EventCategory\GetEditEventCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditEventCategoryController extends ApiController
{
    public function __construct(
        private readonly GetEditEventCategoryService $getEditEventCategoryService,
    ) {
    }

    #[Route('/event-category/{id}', name: 'api_event_category_edit_details', methods: ['GET'])]
    public function __invoke(EventCategory $eventCategory): Response
    {
        $this->denyAccessUnlessGranted(EventCategorySecurityVoter::EVENT_CATEGORY_MANAGE, $eventCategory);
        $categoryDetails = $this->getEditEventCategoryService->getEditEventCategoryDetails($eventCategory);

        return $this->createSuccessResponse($categoryDetails);
    }
}
