<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EventEnrollmentNotFoundException extends NotFoundHttpException
{
    public static function forEnrollmentAndSession(int $enrollmentId, int $sessionId): self
    {
        return new self(
            sprintf(
                'EventEnrollment %d not found for session %d.',
                $enrollmentId,
                $sessionId
            )
        );
    }
}
