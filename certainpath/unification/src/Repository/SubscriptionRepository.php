<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Subscription;
use Doctrine\Persistence\ManagerRegistry;

class SubscriptionRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function saveSubscription(Subscription $subscription): Subscription
    {
        /** @var Subscription $savedSubscription */
        $savedSubscription = $this->save($subscription);
        return $savedSubscription;
    }

    public function deleteAllSubscriptionsForCompanyExcludingCustomerVersion(
        Company $company,
        string $version,
    ): void {
        $this->createQueryBuilder('s')
            ->delete()
            ->where('s.company = :company')
            ->andWhere('s.customer IN (
            SELECT c.id FROM App\Entity\Customer c 
            WHERE c.version != :version
        )')
            ->setParameter('company', $company)
            ->setParameter('version', $version)
            ->getQuery()
            ->execute();
    }
}
