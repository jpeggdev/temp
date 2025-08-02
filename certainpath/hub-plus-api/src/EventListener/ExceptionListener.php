<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $statusCode = $this->getStatusCode($exception);

        $errors = [
            'status' => (string) $statusCode,
            'title' => $this->getTitle($exception),
            'detail' => $exception->getMessage(),
        ];

        if (
            ($previous = $exception->getPrevious())
            && $previous instanceof ValidationFailedException
        ) {
            $violations = $previous->getViolations();
            $errors = $this->formatValidationErrors($violations);
        }

        $errorResponse['errors'] = $errors;
        $response = new JsonResponse($errorResponse, $statusCode);

        $event->setResponse($response);
    }

    private function getStatusCode(\Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    private function getTitle(\Throwable $exception): string
    {
        return (new \ReflectionClass($exception))->getShortName();
    }

    private function formatValidationErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'status' => (string) Response::HTTP_UNPROCESSABLE_ENTITY,
                'title' => 'Invalid Attribute',
                'detail' => $violation->getMessage(),
                'source' => [
                    'pointer' => '/data/attributes/'.$violation->getPropertyPath(),
                ],
            ];
        }

        return $errors;
    }
}
