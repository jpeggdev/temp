<?php

namespace App\QueryBuilder;

use App\Entity\Batch;
use Doctrine\ORM\QueryBuilder;

readonly class BatchProspectQueryBuilder extends AbstractQueryBuilder
{
    protected function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('b')
            ->from(Batch::class, 'b');
    }

    public function prepareBulkInsertBatchProspectsSQLQuery(array $batchProspectData): array
    {
        $sql = 'INSERT INTO batch_prospect (batch_id, prospect_id) VALUES ';
        $values = [];
        $params = [];

        foreach ($batchProspectData as $data) {
            $values[] = "(?, ?)";
            $params[] = $data['batch_id'];
            $params[] = $data['prospect_id'];
        }

        $sql .= implode(', ', $values);
        return [$sql, $params];
    }
}
