<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\CreditMemoLineItem;
use App\Enum\CreditMemoType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CreditMemoLineItem>
 */
class CreditMemoLineItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreditMemoLineItem::class);
    }

    public function save(CreditMemoLineItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countVoucherLineItemsForCompany(Company $company): int
    {
        return (int) $this->createQueryBuilder('cml')
            ->select('COUNT(cml.id)')
            ->leftJoin('cml.creditMemo', 'cm')
            ->leftJoin('cm.invoice', 'i')
            ->where('cm.type = :type')
            ->andWhere('i.company = :company')
            ->setParameter('type', CreditMemoType::VOUCHER)
            ->setParameter('company', $company)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
