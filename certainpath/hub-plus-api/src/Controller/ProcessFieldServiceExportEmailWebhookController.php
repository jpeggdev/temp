<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ProcessFieldServiceExportEmailWebhookService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/public/webhook')]
class ProcessFieldServiceExportEmailWebhookController extends AbstractController
{
    public function __construct(
        private readonly ProcessFieldServiceExportEmailWebhookService $webhookService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(
        '/process-field-service-export-email',
        name: 'process_field_service_export_email_webhook',
        methods: ['POST']
    )]
    public function __invoke(Request $request): Response
    {
        if (!$this->webhookService->authenticateWebhook($request)) {
            $this->logger->error('Invalid webhook signature.');

            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $this->webhookService->processWebhook($request);

        return new JsonResponse(['message' => 'Webhook processed successfully'], Response::HTTP_OK);
    }
}
