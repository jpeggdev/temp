<?php

namespace App\Services;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Entity\Location;
use App\Exceptions\NotFoundException\LocationNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\LocationRepository;
use App\Repository\ProspectRepository;

readonly class ProspectAggregatedService
{
    public function __construct(
        private ProspectRepository $prospectRepository,
        private LocationRepository $locationRepository,
    ) {
    }

    /**
     * @throws LocationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     */
    public function getProspectsAggregatedData(
        ProspectFilterRulesDTO $dto
    ): array {
        $this->initPostalCodesValue($dto);
        $aggregatedData = $this->prospectRepository->getProspectsAggregatedData($dto);

        return $this->formatData($aggregatedData);
    }

    private function formatData(array $aggregatedData): array
    {
        return array_map(static function ($item) {
            if (isset($item['avgSales'])) {
                $item['avgSales'] = (int)$item['avgSales'];
            }
            return $item;
        }, $aggregatedData);
    }

    /**
     * @throws LocationNotFoundException
     *
     * For each location ID provided in the DTO, this method fetches the corresponding Location entity,
     * extracts its associated postal codes, and populates the postalCodes array in the DTO.
     */
    private function initPostalCodesValue(ProspectFilterRulesDTO $dto): void
    {
        $locationPostalCodes = [];

        /** @var Location $location */
        foreach($dto->locations as $location) {
            $location = $this->locationRepository->findOneByIdOrFail((int)$location);
            foreach ($location->getPostalCodes() as $postalCode) {
                $locationPostalCodes[] = $postalCode;
            }
        }

        $dto->postalCodes = $locationPostalCodes;
    }
}
