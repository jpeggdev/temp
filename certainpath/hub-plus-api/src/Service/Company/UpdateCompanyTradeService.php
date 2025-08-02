<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\DTO\Request\Company\UpdateCompanyTradeDTO;
use App\DTO\Response\Company\UpdateCompanyTradesResponseDTO;
use App\Entity\Company;
use App\Entity\CompanyTrade;
use App\Exception\TradeNotFoundException;
use App\Repository\TradeRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateCompanyTradeService
{
    public function __construct(
        private TradeRepository $tradeRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateCompanyTrade(Company $company, UpdateCompanyTradeDTO $dto): UpdateCompanyTradesResponseDTO
    {
        $trade = $this->tradeRepository->find($dto->tradeId);

        if (!$trade) {
            throw new TradeNotFoundException();
        }

        // Find an existing CompanyTrade association
        $existingCompanyTrade = $company->getCompanyTrades()->filter(
            fn (CompanyTrade $companyTrade) => $companyTrade->getTrade() === $trade
        )->first();

        if ($existingCompanyTrade) {
            // If the association exists, remove it
            $company->removeCompanyTrade($existingCompanyTrade);
            $this->entityManager->remove($existingCompanyTrade);
        } else {
            // Otherwise, create a new CompanyTrade association
            $newCompanyTrade = new CompanyTrade();
            $newCompanyTrade->setCompany($company);
            $newCompanyTrade->setTrade($trade);
            $company->addCompanyTrade($newCompanyTrade);

            $this->entityManager->persist($newCompanyTrade);
        }

        $this->entityManager->flush();

        return UpdateCompanyTradesResponseDTO::fromEntity($company);
    }
}
