<?php

namespace App\Repository;

use App\Entity\EmailTemplate;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Request\GetEmailTemplatesDTO;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Exception\EmailTemplateNotFoundException;
use App\QueryBuilder\EmailTemplateQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailTemplate>
 */
class EmailTemplateRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EmailTemplateQueryBuilder $emailTemplateQueryBuilder,
    ) {
        parent::__construct($registry, EmailTemplate::class);
    }

    public function save(EmailTemplate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmailTemplate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllByQuery(GetEmailTemplatesDTO $queryDto): array
    {
        $qb = $this->createQueryBuilder('et')
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->orderBy('et.'.$queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(GetEmailTemplatesDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('et')
            ->select('COUNT(et.id)');

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findOneById(int $id): ?EmailTemplate
    {
        return $this->emailTemplateQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): EmailTemplate
    {
        $result = $this->findOneById($id);

        if (!$result) {
            throw new EmailTemplateNotFoundException();
        }

        return $result;
    }

    /**
     * @return ArrayCollection<int, EmailTemplate>
     */
    public function findAllActive(): ArrayCollection
    {
        $result = $this->emailTemplateQueryBuilder
            ->createFindAllActiveQueryBuilder()
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    private function applyFilters(QueryBuilder $qb, GetEmailTemplatesDTO $queryDto): void
    {
        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(et.name) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%'.strtolower($queryDto->searchTerm).'%');
        }

        if (null !== $queryDto->isActive) {
            $qb->andWhere('et.isActive = :isActive')
                ->setParameter('isActive', $queryDto->isActive);
        }
    }
}
