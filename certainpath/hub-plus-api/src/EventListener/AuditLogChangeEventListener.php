<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Contract\Entity\AuditableInterface;
use App\DTO\LogEventDTO;
use App\Enum\AuditLogOperation;
use App\Service\AuditLogCreationService;
use App\Service\AuditLogSerializationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postRemove, priority: 500, connection: 'default')]
readonly class AuditLogChangeEventListener
{
    public function __construct(
        private AuditLogSerializationService $serializationService,
        private AuditLogCreationService $creationService,
        private Security $security,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        if (!$args->getObject() instanceof AuditableInterface) {
            return;
        }
        $this->log(AuditLogOperation::CREATE, $args);
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        if (!$args->getObject() instanceof AuditableInterface) {
            return;
        }
        $this->log(AuditLogOperation::UPDATE, $args);
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        if (!$args->getObject() instanceof AuditableInterface) {
            return;
        }
        $this->log(AuditLogOperation::DELETE, $args);
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    private function log(AuditLogOperation $operationType, LifecycleEventArgs $args): void
    {
        $changeSet = (AuditLogOperation::DELETE != $operationType) ?
            $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($args->getObject()) : [];
        $changeSetSerialized = $this->serializationService->serialize($args->getObject(), $operationType, $changeSet);

        $logEventDTO = new LogEventDTO(
            $args->getObject(),
            $this->security->getUser(),
            $operationType,
            $changeSetSerialized
        );
        $this->creationService->logAuditEvent($logEventDTO);
    }
}
