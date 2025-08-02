<?php

namespace App\Repository;

use App\DTO\Request\GetEmailTemplateCategoriesDTO;
use App\Entity\EmailTemplateCategory;
use App\Exception\NotFoundException\EmailTemplateCategoryNotFoundException;
use App\QueryBuilder\EmailTemplateCategoryQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailTemplateCategory>
 */
class EmailTemplateCategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EmailTemplateCategoryQueryBuilder $emailTemplateCategoryQueryBuilder,
    ) {
        parent::__construct($registry, EmailTemplateCategory::class);
    }

    public function save(EmailTemplateCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByName(string $name): ?EmailTemplateCategory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByQuery(GetEmailTemplateCategoriesDTO $dto): array
    {
        $qb = $this->createQueryBuilder('etc');

        $this->applyFilters($qb, $dto);

        $qb->setMaxResults($dto->pageSize)
            ->setFirstResult(($dto->page - 1) * $dto->pageSize)
            ->orderBy('etc.'.$dto->sortBy, $dto->sortOrder);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(GetEmailTemplateCategoriesDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('etc')
            ->select('COUNT(etc.id)');

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findOneById(int $id): ?EmailTemplateCategory
    {
        return $this->emailTemplateCategoryQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): EmailTemplateCategory
    {
        $result = $this->findOneById($id);

        if (!$result) {
            throw new EmailTemplateCategoryNotFoundException();
        }

        return $result;
    }

    private function applyFilters(QueryBuilder $qb, GetEmailTemplateCategoriesDTO $dto): void
    {
        if ($dto->name) {
            $qb->andWhere('LOWER(etc.displayedName) LIKE LOWER(:searchName)')
                ->setParameter('searchName', '%'.$dto->name.'%');
        }
    }
}
