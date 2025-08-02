<?php

namespace App\Controller\API\Prospects;

use App\Controller\API\ApiController;
use App\DTO\Request\Prospect\UpdateProspectDoNotMailDTO;
use App\Services\Prospect\UpdateProspectService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class UpdateProspectDoNotMailController extends ApiController
{
    public function __construct(
        private readonly UpdateProspectService $updateProspectService,
    ) {
    }

    #[Route('/api/prospects/{id}/do-not-mail', name: 'api_prospect_patch_do_not_mail', methods: ['PATCH', 'POST'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateProspectDoNotMailDTO $updateProspectDoNotMailDTO
    ): Response {
        $prospect = $this->updateProspectService->updateProspect($id, $updateProspectDoNotMailDTO);

        return $this->createJsonSuccessResponse([
            'id' => $prospect->getId(),
            'doNotMail' => $prospect->isDoNotMail(),
        ]);
    }
}