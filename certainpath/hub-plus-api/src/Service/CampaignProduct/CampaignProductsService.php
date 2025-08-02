<?php

declare(strict_types=1);

namespace App\Service\CampaignProduct;

use App\DTO\CampaignProduct\CampaignProductResponseDTO;
use App\Entity\CampaignProduct;
use App\Repository\CampaignProductRepository;

readonly class CampaignProductsService
{
    public function __construct(
        private CampaignProductRepository $campaignProductRepository,
    ) {
    }

    /**
     * @return array{
     *     campaignProducts: CampaignProductResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getProducts(): array
    {
        $campaignProducts = $this->campaignProductRepository->fetchCampaignProducts();
        $totalCount = count($campaignProducts);

        $campaignProductDTOs = array_map(
            static fn (CampaignProduct $campaignProduct) => CampaignProductResponseDTO::fromEntity($campaignProduct),
            $campaignProducts
        );

        return [
            'campaignProducts' => $campaignProductDTOs,
            'totalCount' => $totalCount,
        ];
    }
}
