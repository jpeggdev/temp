<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Request\UserQueryDTO;
use App\Entity\BusinessRole;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventSession;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Employee>
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function save(Employee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findEmployeesByQuery(UserQueryDTO $queryDto, Company $company): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->addSelect('u')
            ->where('e.company = :companyId')
            ->setParameter('companyId', $company->getId())
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize);

        if (null === $queryDto->sortBy) {
            $qb->orderBy('u.lastName', $queryDto->sortOrder)
                ->addOrderBy('u.firstName', $queryDto->sortOrder);
        } else {
            $qb->orderBy('u.'.$queryDto->sortBy, $queryDto->sortOrder);
        }

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(UserQueryDTO $queryDto, Company $company): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->leftJoin('e.user', 'u')
            ->where('e.company = :companyId')
            ->setParameter('companyId', $company->getId());

        $this->applyFilters($qb, $queryDto);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findOneMatchingEmailAndCompany(string $email, Company $company): ?Employee
    {
        return $this->createQueryBuilder('e')
            ->join('e.user', 'u')
            ->andWhere('u.email = :email')
            ->andWhere('e.company = :company')
            ->setParameter('email', $email)
            ->setParameter('company', $company)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findEmployeeForCompany(UserInterface $user, string $companyUuid): ?Employee
    {
        return $this->createQueryBuilder('e')
            ->join('e.company', 'c')
            ->where('e.user = :user')
            ->andWhere('c.uuid = :companyUuid')
            ->setParameter('user', $user)
            ->setParameter('companyUuid', $companyUuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCertainPathEmployee(UserInterface $user): ?Employee
    {
        return $this->createQueryBuilder('e')
            ->join('e.company', 'c')
            ->where('e.user = :user')
            ->andWhere('c.certainPath = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findFirstEmployeeForUser(UserInterface $user): ?Employee
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByUser(User $user): ?Employee
    {
        return $this->findOneBy(['user' => $user]);
    }

    /**
     * Find an employee by ID.
     *
     * @param int $id The employee ID
     *
     * @return Employee|null The employee entity or null if not found
     */
    public function findEmployeeById(int $id): ?Employee
    {
        return $this->find($id);
    }

    /**
     * Find an employee by user ID.
     *
     * @param int $userId The user ID
     *
     * @return Employee|null The employee entity or null if not found
     */
    public function findEmployeeByUserId(int $userId): ?Employee
    {
        return $this->createQueryBuilder('e')
            ->join('e.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function applyFilters(QueryBuilder $qb, UserQueryDTO $queryDto): void
    {
        if ($queryDto->searchTerm) {
            $searchTerm = '%'.strtolower($queryDto->searchTerm).'%';
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(u.firstName)', ':searchTerm'),
                    $qb->expr()->like('LOWER(u.lastName)', ':searchTerm'),
                    $qb->expr()->like('LOWER(u.email)', ':searchTerm')
                )
            )
                ->setParameter('searchTerm', $searchTerm);
        }

        if ($queryDto->firstName) {
            $qb->andWhere('LOWER(u.firstName) LIKE LOWER(:firstName)')
                ->setParameter('firstName', '%'.strtolower($queryDto->firstName).'%');
        }

        if ($queryDto->lastName) {
            $qb->andWhere('LOWER(u.lastName) LIKE LOWER(:lastName)')
                ->setParameter('lastName', '%'.strtolower($queryDto->lastName).'%');
        }

        if ($queryDto->email) {
            $qb->andWhere('LOWER(u.email) LIKE LOWER(:email)')
                ->setParameter('email', '%'.strtolower($queryDto->email).'%');
        }

        if ($queryDto->salesforceId) {
            $qb->andWhere('u.salesforceId = :salesforceId')
                ->setParameter('salesforceId', $queryDto->salesforceId);
        }
    }

    public function saveEmployee(Employee $employeeToSave): void
    {
        $this->save($employeeToSave, true);
    }

    public function getEmployeesMatchingRole(
        BusinessRole $role,
    ): array {
        return $this->findBy(
            [
                'role' => $role,
            ]
        );
    }

    public function getEmployeesMatchingRoles(array $roles): array
    {
        if (empty($roles)) {
            return [];
        }

        $qb = $this->createQueryBuilder('e');
        $qb->where('e.role IN (:roles)')
            ->setParameter('roles', $roles);

        return $qb->getQuery()->getResult();
    }

    public function findEmployeeByUuid(string $employeeUuid): ?Employee
    {
        return $this->findOneBy(['uuid' => $employeeUuid]);
    }

    public function findAllNotEnrolledInSessionByCompany(
        EventSession $session,
        Company $company,
    ): array {
        $qb = $this->createQueryBuilder('emp');

        $qb->where('emp.company = :companyId')
            ->andWhere(
                $qb->expr()->not(
                    $qb->expr()->exists(
                        'SELECT 1
                         FROM App\Entity\EventEnrollment ee
                         WHERE ee.employee = emp.id
                         AND ee.eventSession = :sessionId'
                    )
                )
            )
            ->setParameter('companyId', $company->getId())
            ->setParameter('sessionId', $session->getId());

        return $qb->getQuery()->getResult();
    }
}
