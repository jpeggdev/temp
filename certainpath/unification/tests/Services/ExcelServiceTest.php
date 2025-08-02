<?php

namespace App\Tests\Services;

use App\Tests\Services\DMER\DMERTestCase;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Utils;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Model\WorkbookWorksheet;

class ExcelServiceTest extends DMERTestCase
{
    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function testCreateExcelSession(): void
    {
        $fileExists = $this->getOneDriveService()->fileExists($this->company, 'test.xlsm');
        $this->assertTrue($fileExists);

        $promise = $this->getExcelService()->createExcelSession('test.xlsm', $this->company);
        Utils::settle($promise)->wait();

        $result = $this->getExcelService()->createExcelSession('test.xlsm', $this->company);
        $this->assertNotNull($promise);
        $this->getExcelService()->closeWorkbookSession('test.xlsm', $this->company, $promise);
    }

    public function testGetExcelWorkbooks()
    {
        $result = $this->getExcelService()->getExcelWorkbooks('test.xlsm', $this->company);
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertCount(14, $result);
        $this->assertContainsOnlyInstancesOf(WorkbookWorksheet::class, $result);
    }
}
