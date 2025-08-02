<?php

namespace App\Tests\Services\DMER;

use App\Entity\Company;
use App\Entity\Trade;
use App\Exceptions\OneDriveException;
use App\Services\ExcelService;
use App\Services\OneDriveService;
use App\Tests\FunctionalTestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Utils;
use Microsoft\Graph\Exception\GraphException;

class DMERTestCase extends FunctionalTestCase
{
    protected Company $company;

    /**
     * @throws GraphException
     * @throws OneDriveException
     * @throws GuzzleException
     */
    public function setUp(): void
    {
        parent::setUp();

        $trade = Trade::electrical();

        $this->company = (new Company())
            ->setName('Test Company')
            ->setIdentifier('TEST1')
            ->addTrade($trade);

        $this->getOneDriveService()->populateBaseDirectories();
        $this->getOneDriveService()->createDirectoryResourceForCompany($this->company);

        $fileExists = $this->getOneDriveService()->fileExists($this->company, 'test.xlsm');

        if (!$fileExists) {
            $promise = $this->getOneDriveService()->copyFile(
                $this->company,
                'YTD-Simple-DMER_BUDGET-SL-2023.xlsm',
                'test.xlsm'
            );

            Utils::settle($promise)->wait();
            $message = <<<EOF
*** Writing a file that future tests will rely on.
If you get a failure, try running the tests again
after OneDrive has a chance to recognize the file.\n
EOF;
            fwrite(
                STDOUT,
                $message
            );
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }


    protected function getExcelService(): ExcelService
    {
        return $this->getService(
            ExcelService::class
        );
    }

    protected function getOneDriveService(): OneDriveService
    {
        return $this->getService(
            OneDriveService::class
        );
    }
}
