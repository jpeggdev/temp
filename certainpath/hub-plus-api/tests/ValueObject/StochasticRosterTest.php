<?php

namespace App\Tests\ValueObject;

use App\Tests\AbstractKernelTestCase;
use App\ValueObject\StochasticRoster;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class StochasticRosterTest extends AbstractKernelTestCase
{
    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function testStochasticRoster(): void
    {
        $client = $this->getFileClient();
        $files = $client->list(
            'stochastic-files',
            'roster/'
        );
        self::assertCount(6, $files);

        $excelFiles = array_filter($files, static function ($file) {
            return 'xlsx' === pathinfo($file, PATHINFO_EXTENSION);
        });

        foreach ($excelFiles as $excelFile) {
            //            $this->debugString($excelFile);
            $downloadedFile = $client->download(
                'stochastic-files',
                $excelFile
            );
            $roster = StochasticRoster::fromExcelFile(
                $downloadedFile
            );
            foreach ($roster->getRoster() as $company) {
                // assert string starts with SM or PS
                if (!empty($company->intacctId)) {
                    self::assertMatchesRegularExpression(
                        '/^(SM|PS|AT|ES)/',
                        $company->intacctId,
                        $company->intacctId
                        .' '
                        .$company->fileName
                    );
                }
            }
        }
    }
}
