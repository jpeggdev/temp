<?php

namespace App\Repository;

use App\Entity\Batch;
use App\Entity\CampaignIterationWeek;
use App\QueryBuilder\BatchProspectQueryBuilder;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

class BatchProspectRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly BatchProspectQueryBuilder $batchProspectQueryBuilder,
    ) {
        parent::__construct($registry, CampaignIterationWeek::class);
    }

    /**
     * @throws Exception
     */
    public function bulkInsertBatchProspects(Batch $batch, Collection $prospects): void
    {
        $count = 0;
        $batchSize = 200;
        $batchProspectData = [];

        $connection = $this->getEntityManager()->getConnection();

        foreach ($prospects as $prospect) {
            $batchProspectData[] = [
                'batch_id' => $batch->getId(),
                'prospect_id' => $prospect->getId(),
            ];

            $count++;

            if ($count % $batchSize === 0) {
                [$sql, $params] = $this->batchProspectQueryBuilder->prepareBulkInsertBatchProspectsSQLQuery($batchProspectData);
                $connection->executeStatement($sql, $params);
                $batchProspectData = [];
            }
        }

        if (!empty($batchProspectData)) {
            [$sql, $params] = $this->batchProspectQueryBuilder->prepareBulkInsertBatchProspectsSQLQuery($batchProspectData);
            $connection->executeStatement($sql, $params);
        }
    }
}
