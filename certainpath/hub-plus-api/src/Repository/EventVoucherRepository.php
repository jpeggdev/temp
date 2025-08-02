<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\EventVoucher;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Query\GetEventVouchersDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Exception\EventVoucherNotFoundException;
use App\QueryBuilder\EventVoucherQueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventVoucher>
 */
class EventVoucherRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EventVoucherQueryBuilder $eventVoucherQueryBuilder,
    ) {
        parent::__construct($registry, EventVoucher::class);
    }

    public function save(EventVoucher $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function softDelete(EventVoucher $entity, bool $flush = false): void
    {
        $entity
            ->setIsActive(false)
            ->setDeletedAt(new \DateTimeImmutable('now'));

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneById(int $id): ?EventVoucher
    {
        return $this->eventVoucherQueryBuilder
            ->createFindOneByIdQueryBuilder($id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByIdOrFail(int $id): EventVoucher
    {
        $result = $this->findOneById($id);

        if (!$result) {
            throw new EventVoucherNotFoundException();
        }

        return $result;
    }

    public function findOneByCode(string $code): ?EventVoucher
    {
        return $this->eventVoucherQueryBuilder
            ->createFindOneByNameQueryBuilder($code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return ArrayCollection<int, EventVoucher>
     */
    public function findAllByDTO(
        GetEventVouchersDTO $queryDto,
    ): ArrayCollection {
        $firstResult = ($queryDto->page - 1) * $queryDto->pageSize;
        $sortBy = EventVoucherQueryBuilder::ALIAS.'.'.$queryDto->sortBy;

        $result = $this->eventVoucherQueryBuilder
            ->createFindAllByDTOQueryBuilder($queryDto)
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult($firstResult)
            ->orderBy($sortBy, $queryDto->sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function getCountByDTO(GetEventVouchersDTO $queryDto): int
    {
        return $this->eventVoucherQueryBuilder
            ->createGetCountByDTOQueryBuilder($queryDto)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllByCompany(Company $company): array
    {
        return $this->createQueryBuilder('ev')
            ->where('ev.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }
}
