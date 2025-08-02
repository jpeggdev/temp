<?php

namespace App\Service;

use App\DTO\CampaignProduct\CampaignProductRequestDTO;
use App\DTO\CampaignProduct\CampaignProductResponseDTO;
use App\Entity\CampaignProduct;
use App\Repository\CampaignProductRepository;
use Doctrine\ORM\EntityNotFoundException;

readonly class CampaignProductService
{
    public function __construct(
        private CampaignProductRepository $repository,
    ) {
    }

    public function createCampaignProductFromRequestDTO(
        CampaignProductRequestDTO $requestDTO,
    ): CampaignProductResponseDTO {
        $campaignProduct = new CampaignProduct();

        return $this->populate($campaignProduct, $requestDTO);
    }

    public function getCampaignProductResponseDTOById(int $id): CampaignProductResponseDTO
    {
        $campaignProduct = $this->repository->findOneActiveById($id);

        if (!$campaignProduct) {
            throw new EntityNotFoundException('CampaignProduct not found.');
        }

        return CampaignProductResponseDTO::fromEntity($campaignProduct);
    }

    public function updateCampaignProductFromRequestDTO(
        int $id,
        CampaignProductRequestDTO $requestDTO,
    ): CampaignProductResponseDTO {
        $campaignProduct = $this->repository->findOneActiveById($id);

        if (!$campaignProduct) {
            throw new EntityNotFoundException('CampaignProduct not found.');
        }

        return $this->populate($campaignProduct, $requestDTO);
    }

    public function deactivateCampaignProductById(int $id): void
    {
        $campaignProduct = $this->repository->findOneActiveById($id);

        if (!$campaignProduct) {
            throw new EntityNotFoundException('CampaignProduct not found.');
        }

        $this->repository->deactivateCampaignProduct($campaignProduct);
    }

    private function populate(
        CampaignProduct $campaignProduct,
        CampaignProductRequestDTO $requestDTO,
    ): CampaignProductResponseDTO {
        $campaignProduct->setName($requestDTO->name);
        $campaignProduct->setType(
            $requestDTO->type ??
                CampaignProduct::CAMPAIGN_PRODUCT_TYPES[0]
        );
        $campaignProduct->setDescription($requestDTO->description);
        $campaignProduct->setCategory(
            $requestDTO->category ??
                CampaignProduct::CAMPAIGN_PRODUCT_CATEGORIES[0]
        );
        $campaignProduct->setSubCategory(
            $requestDTO->subCategory ??
            CampaignProduct::CAMPAIGN_PRODUCT_SUBCATEGORIES[0]
        );
        $campaignProduct->setFormat($requestDTO->format ?? null);
        $campaignProduct->setProspectPrice((string) $requestDTO->prospectPrice);
        $campaignProduct->setCustomerPrice((string) $requestDTO->customerPrice);
        $campaignProduct->setMailerDescription(
            $requestDTO->mailerDescription ??
                $requestDTO->description
        );
        $campaignProduct->setCode(
            $requestDTO->code ??
                CampaignProduct::CAMPAIGN_PRODUCT_CODES[0]
        );
        $campaignProduct->setHasColoredStock($requestDTO->hasColoredStock ?? false);
        $campaignProduct->setBrand($requestDTO->brand ?? null);
        $campaignProduct->setSize($requestDTO->size ?? null);
        $campaignProduct->setDistributionMethod(
            $requestDTO->distributionMethod ??
                CampaignProduct::CAMPAIGN_PRODUCT_DISTRIBUTION_METHODS[0]
        );
        $campaignProduct->setTargetAudience(
            $requestDTO->targetAudience ??
                CampaignProduct::CAMPAIGN_PRODUCT_TARGET_AUDIENCES[0]
        );

        $campaignProduct = $this->repository->saveCampaignProduct($campaignProduct);

        return CampaignProductResponseDTO::fromEntity($campaignProduct);
    }

    public function getProducts(): array
    {
        $campaignProducts = $this->repository->findAll();
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
