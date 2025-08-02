<?php

namespace App\Repository;

use App\DTO\Query\Tag\TagQueryDTO;
use App\Entity\Company;
use App\Entity\Tag;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Tag>
 */
class TagRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Tag::class);
    }

    public function saveTag(Tag $tag): Tag
    {
        /** @var Tag $saved */
        $saved = $this->save($tag);
        return $saved;
    }

    public function findOneByCompanyAndTagName(
        Company $company,
        string $tagName
    ): ?Tag {
        return $this->createQueryBuilder('t')
            ->where('t.company = :company')
            ->andWhere('t.name = :tagName')
            ->setParameter('company', $company)
            ->setParameter('tagName', $tagName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Tag[]
     */
    public function findByQuery(TagQueryDTO $queryDto): array
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.company', 'c')
            ->addSelect('c')
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->orderBy('t.' . $queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(TagQueryDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.company', 'c')
            ->select('COUNT(t.id)');

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(
        QueryBuilder $qb,
        TagQueryDTO $queryDto
    ): void {

        if ($queryDto->systemTags === false) {
            $qb->andWhere('t.isSystem = :systemTags')
                ->setParameter('systemTags', $queryDto->systemTags);
        }

        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(t.name) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . strtolower($queryDto->searchTerm) . '%');
        }

        if ($queryDto->companyIdentifier) {
            $qb->andWhere('c.identifier = :companyIdentifier')
                ->setParameter('companyIdentifier', $queryDto->companyIdentifier);
        }
    }
}
