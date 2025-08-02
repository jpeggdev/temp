<?php

namespace App\Service\Company;

use App\DTO\Response\Company\GetEditCompanyDetailsResponseDTO;
use App\Entity\Company;
use App\Repository\FieldServiceSoftwareRepository;
use App\Repository\TradeRepository;

readonly class GetEditCompanyDetailsService
{
    public function __construct(
        private FieldServiceSoftwareRepository $fieldServiceSoftwareRepository,
        private TradeRepository $tradeRepository,
    ) {
    }

    public function getEditCompanyDetails(Company $company): GetEditCompanyDetailsResponseDTO
    {
        $fieldServiceSoftware = $company->getFieldServiceSoftware();
        $allFieldServiceSoftware = $this->fieldServiceSoftwareRepository->findAll();

        $fieldServiceSoftwareList = array_map(function ($software) {
            return [
                'id' => $software->getId(),
                'name' => $software->getName(),
            ];
        }, $allFieldServiceSoftware);

        $allTrades = $this->tradeRepository->findAll();
        $tradeList = array_map(static function ($trade) {
            return [
                'id' => $trade->getId(),
                'name' => $trade->getName(),
                'description' => $trade->getDescription(),
            ];
        }, $allTrades);

        $companyTrades = $company->getCompanyTrades();
        $companyTradeIds = array_values(array_map(function ($companyTrade) {
            return $companyTrade->getTrade()->getId();
        }, $companyTrades->toArray()));

        return new GetEditCompanyDetailsResponseDTO(
            $company->getCompanyName(),
            $company->getSalesforceId(),
            $company->getIntacctId(),
            $company->isMarketingEnabled(),
            $company->getCompanyEmail(),
            $fieldServiceSoftware?->getId(),
            $fieldServiceSoftware?->getName(),
            $company->getWebsiteUrl(),
            $fieldServiceSoftwareList,
            $tradeList,
            $companyTradeIds
        );
    }
}
