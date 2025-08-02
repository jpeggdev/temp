<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\AuditLogOperation;
use Symfony\Component\Security\Core\User\UserInterface;

class LogEventDTO
{
    public function __construct(
        public object $entity,
        public ?UserInterface $author,
        public AuditLogOperation $operationType,
        public string $eventData,
        public ?string $additionalData = null,
    ) {
    }
}
