<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Repository;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceTitanCredential>
 */
class ServiceTitanCredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceTitanCredential::class);
    }

    public function save(ServiceTitanCredential $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceTitanCredential $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCompanyAndEnvironment(Company $company, ServiceTitanEnvironment $environment): ?ServiceTitanCredential
    {
        return $this->findOneBy([
            'company' => $company,
            'environment' => $environment,
        ]);
    }

    /**
     * @return ServiceTitanCredential[]
     */
    public function findByCompany(Company $company): array
    {
        return $this->findBy([
            'company' => $company,
        ]);
    }

    /**
     * @return ServiceTitanCredential[]
     */
    public function findActiveCredentials(): array
    {
        return $this->createQueryBuilder('stc')
            ->where('stc.connectionStatus = :status')
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ServiceTitanCredential[]
     */
    public function findExpiredTokens(): array
    {
        return $this->createQueryBuilder('stc')
            ->where('stc.tokenExpiresAt IS NOT NULL')
            ->andWhere('stc.tokenExpiresAt <= :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function existsForCompanyAndEnvironment(Company $company, ServiceTitanEnvironment $environment): bool
    {
        $count = $this->createQueryBuilder('stc')
            ->select('COUNT(stc.id)')
            ->where('stc.company = :company')
            ->andWhere('stc.environment = :environment')
            ->setParameter('company', $company)
            ->setParameter('environment', $environment)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
