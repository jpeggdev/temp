<?php

namespace App\Services;

use App\Entity\Company;
use App\Entity\SavedQuery;
use App\Exceptions\QueryManagerException;
use App\Repository\SavedQueryRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManagerInterface;

class QueryManager
{
    public function __construct(
        public readonly SavedQueryRepository $savedQueryRepository,
        public readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws QueryManagerException
     */
    public function validateQuery(Query $query): void
    {
    }

    /**
     * @throws QueryManagerException
     */
    public function saveQuery(
        Company $company,
        Query $query,
        string $entityType,
        $name = null,
        $description = null
    ): SavedQuery {
        $this->validateQuery($query);

        $savedQuery = (new SavedQuery());
        $savedQuery->setCompany($company);
        $savedQuery->setEntityType($entityType);
        $savedQuery->setDql($query->getDQL());
        $savedQuery->setFirstResult($query->getFirstResult());
        $savedQuery->setMaxResults($query->getMaxResults());

        $parameters = [ ];
        foreach ($query->getParameters() as $parameter) {
            $parameters[] = [
                'name' => $parameter->getName(),
                'value' => $parameter->getValue(),
                'type' => $parameter->getType(),
            ];
        }

        $savedQuery->setParameters($parameters);

        if ($name) {
            $savedQuery->setName($name);
        }

        if ($description) {
            $savedQuery->setDescription($description);
        }

        $this->savedQueryRepository->save($savedQuery);

        return $savedQuery;
    }

    public function getResultFromSavedQuery(SavedQuery $savedQuery): array
    {
        $query = $this->entityManager->createQuery($savedQuery->getDql());
        if ($firstResult = $savedQuery->getFirstResult()) {
            $query->setFirstResult($firstResult);
        }

        if ($maxResults = $savedQuery->getMaxResults()) {
            $query->setMaxResults($maxResults);
        }

        foreach ($savedQuery->getParameters() as $parameter) {
            $query->setParameter(
                $parameter['name'],
                $parameter['value'],
                $parameter['type']
            );
        }

        $result = $query->getResult();

        $savedQuery
            ->setRecordCount(count($result))
            ->setLastRun(date_create_immutable())
        ;

        $this->savedQueryRepository->save($savedQuery);

        return $result;
    }
}
