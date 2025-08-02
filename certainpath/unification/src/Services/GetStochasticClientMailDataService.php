<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Request\StochasticClientMailDataQueryDTO;
use App\SQL\GetStochasticClientMailDataSQL;
use Doctrine\DBAL\Exception;

readonly class GetStochasticClientMailDataService
{
    public function __construct(
        private GetStochasticClientMailDataSQL $getStochasticClientMailDataSQL,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getMailData(StochasticClientMailDataQueryDTO $dto): array
    {
        $rows = $this->getStochasticClientMailDataSQL->execute(
            week: $dto->week,
            year: $dto->year,
            page: $dto->page,
            perPage: $dto->perPage,
            sortOrder: $dto->sortOrder
        );

        $total = $this->getStochasticClientMailDataSQL->countTotal(
            week: $dto->week,
            year: $dto->year
        );

        return [
            'items' => $rows,
            'pagination' => [
                'total' => $total,
                'currentPage' => $dto->page,
                'perPage' => $dto->perPage,
            ]
        ];
    }
}
