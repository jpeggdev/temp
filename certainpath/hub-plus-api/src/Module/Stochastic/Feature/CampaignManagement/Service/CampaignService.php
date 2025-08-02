<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Service;

use App\DTO\CampaignProduct\CampaignProductResponseDTO;
use App\DTO\Response\BatchPricingResponseDTO;
use App\DTO\Response\CampaignPricingResponseDTO;
use App\DTO\Response\StochasticClientMailDataRowDTO;
use App\Module\Stochastic\Feature\PostageUploads\Repository\BatchPostageRepository;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Response\CampaignResponseDTO;
use App\Repository\CampaignProductRepository;
use App\Repository\CompanyRepository;

readonly class CampaignService
{
    public function __construct(
        private CampaignProductRepository $campaignProductRepository,
        private BatchPostageRepository $batchPostageRepository,
        private CompanyRepository $companyRepository,
    ) {
    }

    public function hydrateStochasticClientMailDataRowDTO(
        StochasticClientMailDataRowDTO $stochasticClientMailDataRowDTO,
    ): StochasticClientMailDataRowDTO {
        $stochasticClientMailDataRowDTO->clientName =
            $this->companyRepository->findOneByIdentifier(
                $stochasticClientMailDataRowDTO->intacctId
            )?->getCompanyName();
        if (
            $campaignProduct = $this->getCampaignProductByProductId(
                $stochasticClientMailDataRowDTO->productId
            )
        ) {
            $stochasticClientMailDataRowDTO->campaignProduct = $campaignProduct;
            $jobReferenceToUse =
                $stochasticClientMailDataRowDTO->referenceString
                ??
                (string)$stochasticClientMailDataRowDTO->id;
            $batchPricing = $this->getBatchPricingByReference(
                $campaignProduct,
                $jobReferenceToUse,
                $stochasticClientMailDataRowDTO->prospectCount
            );
            $stochasticClientMailDataRowDTO->batchPricing = $batchPricing;
        }

        return $stochasticClientMailDataRowDTO;
    }

    public function hydrateCampaignResponseDTO(
        CampaignResponseDTO $campaignResponseDTO,
    ): CampaignResponseDTO {
        $totalPostageExpense = 0.00;
        $totalMaterialExpense = 0.00;
        $totalUnitsProjected = 0;
        $totalUnitsActual = 0;

        if (
            $campaignProduct = $this->getCampaignProductByProductId(
                $campaignResponseDTO->productId
            )
        ) {
            $campaignResponseDTO->campaignProduct = $campaignProduct;
            $batches = (!empty($campaignResponseDTO->batches)) ? $campaignResponseDTO->batches : [];
            $batchDTOs = [];

            foreach ($batches as $batch) {
                $batchPricing = $this->getBatchPricingByReference(
                    $campaignProduct,
                    (string) $campaignResponseDTO->id,
                    $batch->prospectsCount
                );

                if ($batchPricing) {
                    $totalUnitsProjected += $batchPricing->projectedQuantity ?? 0;
                    $totalUnitsActual += $batchPricing->actualQuantity ?? 0;
                    $totalPostageExpense += $batchPricing->postageExpense ?? 0;
                    $totalMaterialExpense += $batchPricing->materialExpense ?? 0;
                    $batch->batchPricing = $batchPricing;
                }

                $batchDTOs[] = $batch;
            }
            $campaignResponseDTO->batches = $batchDTOs;
            $campaignCanBeBilled = true;
            foreach ($campaignResponseDTO->batches as $batchDTO) {
                if (!$batchDTO->batchPricing?->canBeBilled) {
                    $campaignCanBeBilled = false;
                }
            }

            $totalExpense = ($totalPostageExpense + $totalMaterialExpense);
            $campaignPricingDTO = new CampaignPricingResponseDTO(
                round($totalPostageExpense, 2),
                round($totalMaterialExpense, 2),
                round($totalExpense, 2),
                $totalUnitsActual,
                $totalUnitsProjected,
            );
            $campaignResponseDTO->canBeBilled = $campaignCanBeBilled;
            $campaignResponseDTO->campaignPricing = $campaignPricingDTO;
        }

        return $campaignResponseDTO;
    }

    private function getCampaignProductByProductId(?int $productId): ?CampaignProductResponseDTO
    {
        if (
            null !== $productId
            && $product = $this->campaignProductRepository->findOneActiveById(
                $productId
            )
        ) {
            return CampaignProductResponseDTO::fromEntity($product);
        }

        return null;
    }

    private function computePrice(?CampaignProductResponseDTO $campaignProductResponseDTO): float
    {
        if (null === $campaignProductResponseDTO) {
            return 0.00;
        }

        return round(max([
            (float) $campaignProductResponseDTO->prospectPrice,
            (float) $campaignProductResponseDTO->customerPrice,
        ]), 2);
    }

    private function getBatchPricingByReference(
        CampaignProductResponseDTO $campaignProduct,
        string $reference,
        int $projectedQty,
    ): ?BatchPricingResponseDTO {
        $productPrice = $this->computePrice($campaignProduct);
        if (
            $batchPostage = $this->batchPostageRepository->findOneByReference(
                $reference
            )
        ) {
            $batchPricing = BatchPricingResponseDTO::fromEntity($batchPostage);
            $batchPricing->pricePerPiece = $productPrice;
            $batchPricing->projectedQuantity = $projectedQty;

            $batchPricing->materialExpense = round(
                $productPrice * $batchPricing->actualQuantity,
                2
            );
            $batchPricing->totalExpense = round(
                $batchPricing->materialExpense + $batchPricing->postageExpense,
                2
            );
            $batchPricing->canBeBilled = (
                $batchPricing->materialExpense > 0
                && $batchPricing->postageExpense > 0
                && $batchPricing->actualQuantity > 0
            );

            return $batchPricing;
        }

        return null;
    }
}
