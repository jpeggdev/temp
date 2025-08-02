<?php

namespace App\Repository;

use App\Entity\InvoiceLineItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceLineItem>
 */
class InvoiceLineItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceLineItem::class);
    }

    public function save(InvoiceLineItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countInvoiceLineItemsByDiscountCode(string $discountCode): int
    {
        return (int) $this->createQueryBuilder('li')
            ->select('COUNT(li.id)')
            ->where('li.discountCode = :discountCode')
            ->setParameter('discountCode', $discountCode)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
