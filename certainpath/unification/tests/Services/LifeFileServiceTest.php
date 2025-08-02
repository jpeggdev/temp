<?php

namespace App\Tests\Services;

use App\Entity\Trade;
use App\Tests\FunctionalTestCase;

class LifeFileServiceTest extends FunctionalTestCase
{
    public function testLifeFileListing(): void
    {
        $client = $this->getFileClient();
        $lifeFolderName = 'Standard Air AL Birmingham';
        $files = $client->list(
            'stochastic-files',
            'sync/customer-data/'
            . $lifeFolderName
            . '/4 Power Data/'
        );
        $electricalFound = false;
        $hvacFound = false;
        $plumbingFound = false;
        $roofingFound = false;
        foreach ($files as $file) {
            $fileName = pathinfo($file, PATHINFO_BASENAME);
            if (str_contains($fileName, Trade::ELECTRICAL_CODE)) {
                $electricalFound = true;
            }
            if (str_contains($fileName, Trade::HVAC_CODE)) {
                $hvacFound = true;
            }
            if (str_contains($fileName, Trade::PLUMBING_CODE)) {
                $plumbingFound = true;
            }
            if (str_contains($fileName, Trade::ROOFING_CODE)) {
                $roofingFound = true;
            }
        }

        self::assertTrue($plumbingFound);
        self::assertTrue($hvacFound);
        self::assertFalse($electricalFound);
        self::assertFalse($roofingFound);

        $lifeFileService = $this->getLifeFileService();
        self::assertNotNull($lifeFileService);

        $lifeFileCollection = $lifeFileService->getLifeFileCollection(
            $lifeFolderName
        );

        $lifeFiles = $lifeFileCollection->getLifeFiles();
        self::assertCount(2, $lifeFiles);
        foreach ($lifeFiles as $lifeFile) {
            $trade = $lifeFile->trade;
            self::assertTrue(
                $trade->getName() === Trade::PLUMBING
                || $trade->getName() === Trade::HVAC
            );
            self::assertTrue(
                $trade->isPlumbing()
                ||
                $trade->isHvac()
            );
            self::assertFalse(
                $trade->isRoofing()
                ||
                $trade->isElectrical()
            );
        }
    }
}
