<?php

// src/Controller/EventTag/GetEditEventTagController.php

declare(strict_types=1);

namespace App\Controller\EventTag;

use App\Controller\ApiController;
use App\Entity\EventTag;
use App\Service\EventTag\GetEditEventTagService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditEventTagController extends ApiController
{
    public function __construct(
        private readonly GetEditEventTagService $getEditEventTagService,
    ) {
    }

    #[Route(
        '/event/tag/{id}',
        name: 'api_event_tag_edit_details',
        methods: ['GET']
    )]
    public function __invoke(EventTag $eventTag): Response
    {
        $details = $this->getEditEventTagService
            ->getEditEventTagDetails($eventTag);

        return $this->createSuccessResponse($details);
    }
}
