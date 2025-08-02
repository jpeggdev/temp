<?php

namespace App\Repository;

use App\DTO\Request\Customer\CustomerQueryDTO;
use App\Entity\Company;
use App\Entity\Customer;
use App\Exceptions\EntityValidationException;
use App\QueryBuilder\CustomerQueryBuilder;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomerRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly ValidatorInterface $validator,
        protected readonly CustomerQueryBuilder $customerQueryBuilder,
    ) {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @throws EntityValidationException
     */
    public function validate(Customer $customer): void
    {
        $errors = $this->validator->validate($customer);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            throw new EntityValidationException(implode(' ', $errorMessages));
        }
    }

    /**
     * @return Customer[]
     */
    public function findCustomersById(array $ids): array
    {
        $queryBuilder = $this->createQueryBuilder('customer');

        return $queryBuilder
            ->leftJoin('customer.invoices', 'i')
            ->leftJoin('customer.subscriptions', 's')
            ->addSelect('i', 's')
            ->where($queryBuilder->expr()->in('customer.id', ':ids'))
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Customer[]
     */
    public function findCustomersByQuery(CustomerQueryDTO $queryDto): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.company', 'co')
            ->leftJoin('c.prospect', 'p')
            ->addSelect('co', 'p')
            ->setMaxResults($queryDto->pageSize)
            ->setFirstResult(($queryDto->page - 1) * $queryDto->pageSize)
            ->orderBy('c.'.$queryDto->sortBy, $queryDto->sortOrder);

        $this->applyFilters($qb, $queryDto);

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(CustomerQueryDTO $queryDto): int
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.company', 'co')
            ->select('COUNT(c.id)');

        $this->applyFilters($qb, $queryDto);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(QueryBuilder $qb, CustomerQueryDTO $queryDto): void
    {
        if ($queryDto->searchTerm) {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%'.strtolower($queryDto->searchTerm).'%');
        }

        if ($queryDto->intacctId) {
            $qb->andWhere('co.identifier = :identifier')
                ->setParameter('identifier', $queryDto->intacctId);
        }

        if ($queryDto->isActive !== null) {
            $qb->andWhere('c.isActive = :isActive')
                ->setParameter('isActive', $queryDto->isActive);
        }
    }

    public function saveCustomer(Customer $customer): Customer
    {
        /** @var Customer $saved */
        $saved = $this->save($customer);

        return $saved;
    }

    /**
     * @param Company $company
     * @return Customer[]
     */
    public function findForCompany(Company $company): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.invoices', 'i')
            ->innerJoin('c.company', 'co')
            ->innerJoin('c.prospect', 'p')
            ->addSelect('i', 'co', 'p')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    public function findCustomerById(int $customerId): ?Customer
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.invoices', 'i')
            ->leftJoin('c.subscriptions', 's')
            ->addSelect('i', 's')
            ->where('c.id = :customerId')
            ->setParameter('customerId', $customerId)
            ->getQuery()
            ->getSingleResult();
    }

    public function findOneByIdOrFail(int $id): Customer
    {
        $customer = $this->createQueryBuilder('c')
            ->leftJoin('c.prospect', 'p')
            ->addSelect('p')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
            
        if (!$customer) {
            throw new EntityNotFoundException('Customer not found');
        }

        return $customer;
    }

    public function resetEveryCustomerAsInactiveExcludingCustomerVersion(
        Company $company,
        string $customerVersion
    ): void {
        $this->createQueryBuilder('c')
            ->update()
            ->set('c.isActive', ':isActive')
            ->where('c.company = :company')
            ->andWhere('c.version != :version')
            ->setParameter('isActive', false)
            ->setParameter('company', $company)
            ->setParameter('version', $customerVersion)
            ->getQuery()
            ->execute();
    }

    public function getAllCustomerIdsForCompany(Company $company): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.company = :company')
            //->andWhere('c.isActive = true')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }
}
