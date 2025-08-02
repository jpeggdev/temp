<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\AuditLogOperation;
use App\Serializer\DoctrineEntitySerializer;

readonly class AuditLogSerializationService
{
    public function __construct(private DoctrineEntitySerializer $serializer)
    {
    }

    public function serialize(object $entity, AuditLogOperation $auditLogAction, array $changeSet): string
    {
        return match ($auditLogAction) {
            AuditLogOperation::UPDATE => $this->serializeChangeSet($changeSet),
            default => $this->serializeEntity($entity),
        };
    }

    private function serializeChangeSet(array $changeSet): string
    {
        foreach ($changeSet as $field => $change) {
            $changeSet[$field] = [
                'from' => $change[0],
                'to' => $change[1],
            ];
        }

        return $this->serializer->serializeEntity($changeSet);
    }

    private function serializeEntity(object $entity): string
    {
        return $this->serializer->serializeEntity($entity);
    }
}
