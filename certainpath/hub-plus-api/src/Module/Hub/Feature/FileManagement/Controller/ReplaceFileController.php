<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\Hub\Feature\FileManagement\Service\ReplaceFileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class ReplaceFileController extends ApiController
{
    public function __construct(
        private readonly ReplaceFileService $replaceFileService,
    ) {
    }

    #[Route('/file-manager/files/{fileUuid}/replace', name: 'api_private_file_manager_replace_file', methods: ['POST'])]
    public function __invoke(string $fileUuid, Request $request, LoggedInUserDTO $loggedInUserDTO): Response
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'Replacement file is required'], Response::HTTP_BAD_REQUEST);
        }

        $response = $this->replaceFileService->replaceFile($fileUuid, $file, $loggedInUserDTO);

        return $this->createSuccessResponse($response);
    }
}
