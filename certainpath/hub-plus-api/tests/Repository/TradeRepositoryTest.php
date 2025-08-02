<?php

namespace App\Tests\Repository;

use App\Entity\Trade;
use App\Exception\UnsupportedTrade;
use App\Tests\AbstractKernelTestCase;
use Doctrine\DBAL\Exception;

class TradeRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @throws UnsupportedTrade
     * @throws Exception
     */
    public function testManageTrades(): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM trade'
        );
        $electrical = Trade::electrical();
        $hvac = Trade::hvac();
        $plumbing = Trade::plumbing();
        $roofing = Trade::roofing();

        self::assertEmpty(
            $electrical->getCompanyTrades()
        );

        $hvacFromLongName = Trade::fromLongName(
            $hvac->getLongName()
        );
        self::assertTrue(
            $hvac->is($hvacFromLongName)
        );

        $this->tradeRepository->saveTrade($electrical);
        $this->tradeRepository->saveTrade($hvac);
        $this->tradeRepository->saveTrade($plumbing);
        $this->tradeRepository->saveTrade($roofing);

        $retrievedElectrical = $this->tradeRepository->getTrade(
            Trade::electrical()
        );
        self::assertSame(
            $electrical->getName(),
            $retrievedElectrical->getName()
        );
        self::assertTrue(
            $electrical->is($retrievedElectrical)
        );
        $retrievedHvac = $this->tradeRepository->getTrade(
            Trade::hvac()
        );
        self::assertSame(
            $hvac->getName(),
            $retrievedHvac->getName()
        );
        self::assertTrue(
            $hvac->is($retrievedHvac)
        );
        $retrievedPlumbing = $this->tradeRepository->getTrade(
            Trade::plumbing()
        );
        self::assertSame(
            $plumbing->getName(),
            $retrievedPlumbing->getName()
        );
        self::assertTrue(
            $plumbing->is($retrievedPlumbing)
        );
        $retrievedRoofing = $this->tradeRepository->getTrade(
            Trade::roofing()
        );
        self::assertSame(
            $roofing->getName(),
            $retrievedRoofing->getName()
        );
        self::assertTrue(
            $roofing->is($retrievedRoofing)
        );
    }

    public function testInitializeTrades(): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM trade'
        );
        self::assertNull(
            $this->tradeRepository->getTrade(
                Trade::electrical()
            )
        );
        $this->tradeRepository->initializeTrades();
        self::assertNotNull(
            $this->tradeRepository->getTrade(
                Trade::electrical()
            )
        );
        self::assertNotNull(
            $this->tradeRepository->getTrade(
                Trade::hvac()
            )
        );
        self::assertNotNull(
            $this->tradeRepository->getTrade(
                Trade::plumbing()
            )
        );
        self::assertNotNull(
            $this->tradeRepository->getTrade(
                Trade::roofing()
            )
        );
        self::assertCount(
            4,
            $this->tradeRepository->getAllTrades()
        );
        $this->tradeRepository->initializeTrades();
        self::assertCount(
            4,
            $this->tradeRepository->getAllTrades()
        );
    }
}
