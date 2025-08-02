<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\LogEventDTO;
use App\Entity\AuditLog;
use App\Repository\AuditLogRepository;

readonly class AuditLogCreationService
{
    public function __construct(
        private AuditLogRepository $auditLogRepository,
    ) {
    }

    public function logAuditEvent(
        LogEventDTO $logEventDTO,
    ): void {
        $auditLog = AuditLog::fromLogEventDTO($logEventDTO);
        $this->auditLogRepository->save($auditLog);
    }
}
