<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\StatementBuilder\AbstractStatementBuilder;

readonly abstract class BaseChartDataStatementBuilder extends AbstractStatementBuilder
{
    protected function applyBaseConditions(
        array $conditions,
        array $params,
        string $sql
    ): string {
        return str_replace(
            [
                '-- TRADES_CONDITION',
                '-- YEARS_CONDITION',
                '-- CITIES_CONDITION'
            ],
            [
                !empty($conditions['trades']) && isset($params['trades'])
                    ? 'AND ' . $conditions['trades']
                    : '',
                !empty($conditions['years'])
                    ? ' AND (' . implode(' OR ', $conditions['years']) . ')'
                    : '',
                !empty($conditions['cities']) && isset($params['cities'])
                    ? 'AND ' . $conditions['cities']
                    : '',
            ],
            $sql
        );
    }

    protected function prepareBaseParams(StochasticDashboardDTO $dto): array
    {
        $params = ['intacctId' => $dto->intacctId];

        if (!empty($dto->trades)) {
            $params['trades'] = $dto->trades;
        }

        if (!empty($dto->cities)) {
            $params['cities'] = $dto->cities;
        }

        return $params;
    }

    protected function prepareBaseConditions(StochasticDashboardDTO $dto): array
    {
        $baseConditions = [];

        if (!empty($dto->trades)) {
            $baseConditions['trades'] = $this->getTradesCondition();
        }

        if (!empty($dto->years)) {
            $baseConditions['years'] = $this->getYearsCondition($dto->years);
        }

        if (!empty($dto->cities)) {
            $baseConditions['cities'] = $this->getCitiesCondition();
        }

        return $baseConditions;
    }

    protected function getTradesCondition(): string
    {
        return "t.id = ANY(:trades::INT[])";
    }

    protected function getCitiesCondition(): string
    {
        return "(
            a.id IS NOT NULL AND LOWER(a.city) = ANY (SELECT LOWER(city) FROM UNNEST(:cities::TEXT[]) AS city)
        )";
    }

    protected function getYearsCondition(array $years): array
    {
        return array_map(
            static fn($year) => "EXTRACT(YEAR FROM i.invoiced_at) = {$year}",
            $years
        );
    }
}
