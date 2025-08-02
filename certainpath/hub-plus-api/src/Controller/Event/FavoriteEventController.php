<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\Event;
use App\Service\Event\FavoriteEventService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class FavoriteEventController extends ApiController
{
    public function __construct(
        private readonly FavoriteEventService $favoriteEventService,
    ) {
    }

    #[Route(
        '/events/{uuid}/favorite',
        name: 'api_event_favorite_toggle',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(
        Event $event,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $employee = $loggedInUserDTO->getActiveEmployee();
        $isFavorited = $this->favoriteEventService->toggleFavorite($event, $employee);

        return $this->createSuccessResponse([
            'favorited' => $isFavorited,
        ]);
    }
}
