<?php

namespace App\Collections\Reporting;

use App\DTO\Reports\DMERDailySalesDTO;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DMERSalesCollection extends Collection
{
    private const SORT_ORDER = [
        'Maintenance',
        'Service',
        'Replacement',
        'unknown'
    ];

    public function orderByDateAndServiceCategory(): ?DMERSalesCollection
    {
        $result = $this->sortBy(
            function (DMERDailySalesDTO $item) {
                return array_search($item->getServiceTypeCategory(), self::SORT_ORDER);
            }
        )
            ->groupBy(function (DMERDailySalesDTO $item) {
                return $item->getDate()->format('Y-m-d');
            });
        $now = Carbon::now();
        $period = CarbonPeriod::create($now->copy()->startOfYear(), $now->copy()->lastOfYear());
        foreach ($period as $day) {
            if (!$result->has($day->format('Y-m-d'))) {
                $result->put($day->format('Y-m-d'), new self(
                    [
                        (DMERDailySalesDTO::createEmptyInstance($day->toDateTime()))
                            ->setServiceTypeCategory(self::SORT_ORDER[0]),
                        (DMERDailySalesDTO::createEmptyInstance($day->toDateTime()))
                            ->setServiceTypeCategory(self::SORT_ORDER[1]),
                        (DMERDailySalesDTO::createEmptyInstance($day->toDateTime()))
                            ->setServiceTypeCategory(self::SORT_ORDER[2]),
                        (DMERDailySalesDTO::createEmptyInstance($day->toDateTime()))
                            ->setServiceTypeCategory(self::SORT_ORDER[3])
                    ]
                ));
            } else {
                $dayCollection = $result->get($day->format('Y-m-d'));
                if ($dayCollection->count() < 4) {
                    $result->put($day->format('Y-m-d'), $this->sanitizeDay($dayCollection));
                }
            }
        }
        return $result->sortBy(function ($key, $value) {
            return (Carbon::createFromFormat('Y-m-d', $value))->timestamp;
        });
    }

    private function sanitizeDay(Collection $dayCollection): Collection
    {
        $categories = $dayCollection->map(function (DMERDailySalesDTO $item) {
            return $item->getServiceTypeCategory();
        });
        $missingCategories = array_diff(self::SORT_ORDER, $categories->toArray());
        foreach ($missingCategories as $key => $category) {
            if (!$dayCollection->has($key) || $dayCollection->get($key)->getServiceTypeCategory() != $category) {
                $dayCollection->splice($key, 0, [DMERDailySalesDTO::createEmptyInstance(Carbon::now()->toDateTime())->setServiceTypeCategory($category)]);
            }
        }
        return $dayCollection;
    }
}
