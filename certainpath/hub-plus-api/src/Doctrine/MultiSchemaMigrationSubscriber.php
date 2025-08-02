<?php

namespace App\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * Doctrine migration subscriber to avoid "useless CREATE SCHEMA public/hdb_catalog"
 * in auto-generated migrations.
 */
class MultiSchemaMigrationSubscriber implements EventSubscriber
{
    private array $loadedSchemas = [];

    public function getSubscribedEvents(): array
    {
        return [
            'postGenerateSchema',
        ];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        $requiredSchemas = ['public', 'hdb_catalog'];

        foreach ($requiredSchemas as $ns) {
            if (
                !$schema->hasNamespace($ns)
                && !in_array($ns, $this->loadedSchemas, true)
            ) {
                $schema->createNamespace($ns);
                $this->loadedSchemas[] = $ns;
            }
        }
    }
}
