<?php

namespace App\Repository;

use App\DTO\Request\EmailTemplateVariable\GetEmailTemplateVariablesDTO;
use App\Entity\EmailTemplateVariable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailTemplateVariable>
 */
class EmailTemplateVariableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailTemplateVariable::class);
    }

    public function findAllByQuery(GetEmailTemplateVariablesDTO $queryDto): array
    {
        $qb = $this->createQueryBuilder('etv')
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->orderBy('etv.'.$queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(GetEmailTemplateVariablesDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('etv')
            ->select('COUNT(etv.id)');

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, GetEmailTemplateVariablesDTO $queryDto): void
    {
        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(etv.name) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%'.strtolower($queryDto->searchTerm).'%');
        }
    }
}
