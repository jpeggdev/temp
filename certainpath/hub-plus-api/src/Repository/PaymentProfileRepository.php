<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\PaymentProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentProfile>
 */
class PaymentProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentProfile::class);
    }

    public function findOneByEmployeeAndAuthNetProfiles(
        Employee $employee,
        string $authnetCustomerId,
        string $authnetPaymentProfileId,
    ): ?PaymentProfile {
        return $this->findOneBy([
            'employee' => $employee,
            'authnetCustomerId' => $authnetCustomerId,
            'authnetPaymentProfileId' => $authnetPaymentProfileId,
        ]);
    }
}
