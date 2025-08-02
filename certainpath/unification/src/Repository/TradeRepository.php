<?php

namespace App\Repository;

use App\Entity\Trade;
use App\QueryBuilder\TradeQueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class TradeRepository extends AbstractRepository
{
    private const ALIAS = 't';

    public function __construct(
        ManagerRegistry $registry,
        private readonly TradeQueryBuilder $tradeQueryBuilder
    ) {
        parent::__construct($registry, Trade::class);
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from(Trade::class, self::ALIAS);
    }

    public function saveTrade(Trade $trade): Trade
    {
        /** @var Trade $saved */
        $saved = $this->save($trade);
        return $saved;
    }

    public function findById(int $trade): ?Trade
    {
        return $this->tradeQueryBuilder
            ->createFindByIdQueryBuilder($trade)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchAll(
        int $page,
        int $pageSize,
        string $sortBy,
        string $sortOrder,
        ?string $searchTerm = null,
    ): ArrayCollection {
        $firstResult = ($page - 1) * $pageSize;

        $queryBuilder = $this->createBaseQueryBuilder();
        $queryBuilder = $this->applySearchTermFilter($queryBuilder, $searchTerm);

        $result = $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($pageSize)
            ->addOrderBy(self::ALIAS . '.' . $sortBy, $sortOrder)
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    public function findByName(string $tradeName): ?Trade
    {
        return $this->tradeQueryBuilder
            ->createFindByNameQueryBuilder($tradeName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Trade[]
     */
    public function getAllTrades(): array
    {
        return $this->findAll();
    }

    public function get(Trade $trade): Trade
    {
        return $this->findByName($trade->getName());
    }

    public function fetchAllTradeNames(): ArrayCollection
    {
        $trades = $this->getAllTrades();
        $tradeNames = array_map(static fn($trade) => $trade->getName(), $trades);

        return new ArrayCollection($tradeNames);
    }

    private function applySearchTermFilter(
        QueryBuilder $queryBuilder,
        ?string $searchTerm = null
    ): QueryBuilder {
        if ($searchTerm) {
            $queryBuilder
                ->andWhere('LOWER(' . self::ALIAS . '.name) LIKE LOWER(:searchTerm)')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        return $queryBuilder;
    }
}
