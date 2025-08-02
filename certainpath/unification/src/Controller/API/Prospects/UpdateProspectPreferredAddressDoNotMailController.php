<?php

namespace App\Controller\API\Prospects;

use App\Controller\API\ApiController;
use App\Entity\Address;
use App\Repository\ProspectRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class UpdateProspectPreferredAddressDoNotMailController extends ApiController
{
    public function __construct(
        private readonly ProspectRepository $prospectRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Update do-not-mail status for prospect's preferred address.
     *
     * @param int     $id      Prospect ID
     * @param Request $request HTTP request containing doNotMail payload
     *
     * @return JsonResponse Updated address data or error response
     */
    #[Route(
        '/api/prospects/{id}/preferred-address/do-not-mail',
        name: 'api_prospect_preferred_address_patch_do_not_mail',
        methods: ['PATCH']
    )]
    public function __invoke(int $id, Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $doNotMail = $requestData['doNotMail'] ?? null;

        if ($doNotMail === null) {
            throw new BadRequestHttpException('doNotMail field is required');
        }

        $prospect = $this->prospectRepository->find($id);
        
        if (!$prospect) {
            throw $this->createNotFoundException("No prospect found with ID {$id}");
        }

        /** @var Address $preferredAddress */
        $preferredAddress = $prospect->getPreferredAddress();
        
        if (!$preferredAddress) {
            throw $this->createNotFoundException("No preferred address found for prospect {$id}");
        }

        if ($preferredAddress->isGlobalDoNotMail()) {
            throw new BadRequestHttpException('Cannot update do-not-mail status for a global Do Not Mail address');
        }

        $preferredAddress->setDoNotMail((bool) $doNotMail);
        
        $this->entityManager->flush();

        return $this->createJsonSuccessResponse(
            [
            'id' => $preferredAddress->getId(),
            'doNotMail' => $preferredAddress->isDoNotMail(),
            'prospectId' => $prospect->getId(),
            'updated' => true,
            ]
        );
    }
}