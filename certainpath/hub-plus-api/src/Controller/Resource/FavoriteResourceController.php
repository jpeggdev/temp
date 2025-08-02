<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\Resource;
use App\Service\Resource\FavoriteResourceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class FavoriteResourceController extends ApiController
{
    public function __construct(
        private readonly FavoriteResourceService $favoriteResourceService,
    ) {
    }

    #[Route('/resources/{uuid}/favorite', name: 'api_resource_favorite_toggle', methods: ['POST'])]
    public function __invoke(
        Resource $resource,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $employee = $loggedInUserDTO->getActiveEmployee();
        $isFavorited = $this->favoriteResourceService->toggleFavorite($resource, $employee);

        return $this->createSuccessResponse([
            'favorited' => $isFavorited,
        ]);
    }
}
