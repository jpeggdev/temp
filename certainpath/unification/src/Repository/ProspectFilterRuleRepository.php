<?php

namespace App\Repository;

use App\Entity\ProspectFilterRule;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\QueryBuilder\ProspectFilterRulesQueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

class ProspectFilterRuleRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ProspectFilterRulesQueryBuilder $prospectFilterRulesQueryBuilder
    ) {
        parent::__construct($registry, ProspectFilterRule::class);
    }

    public function saveProspectFilterRule(ProspectFilterRule $prospectFilterRule): ProspectFilterRule
    {
        /** @var ProspectFilterRule $saved */
        $saved = $this->save($prospectFilterRule);
        return $saved;
    }

    public function findByName(string $name): ?ProspectFilterRule
    {
        return $this->prospectFilterRulesQueryBuilder
            ->createFindByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function findByNameOrFail(string $name): ProspectFilterRule
    {
        $result = $this->prospectFilterRulesQueryBuilder
            ->createFindByNameQueryBuilder($name)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result) {
            throw new ProspectFilterRuleNotFoundException();
        }

        return $result;
    }

    public function findByNameAndValue(string $name, mixed $value): ?ProspectFilterRule
    {
        return $this->prospectFilterRulesQueryBuilder
            ->createFindByNameAndValueQueryBuilder($name, $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function findByNameAndValueOrFail(string $name, mixed $value): ProspectFilterRule
    {
        $result = $this->prospectFilterRulesQueryBuilder
            ->createFindByNameAndValueQueryBuilder($name, $value)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result) {
            throw new ProspectFilterRuleNotFoundException();
        }

        return $result;
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    public function findAllByNameOrFail(string $name): ArrayCollection
    {
        $result = $this->prospectFilterRulesQueryBuilder
            ->createFindByNameQueryBuilder($name)
            ->getQuery()
            ->getResult();

        if (!$result) {
            throw new ProspectFilterRuleNotFoundException();
        }

        return new ArrayCollection($result);
    }
}
