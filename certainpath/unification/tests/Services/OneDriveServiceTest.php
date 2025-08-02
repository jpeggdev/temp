<?php

namespace App\Tests\Services;

use App\Tests\Services\DMER\DMERTestCase;
use GuzzleHttp\Exception\ClientException;
use Microsoft\Graph\Model\SharingLink;

class OneDriveServiceTest extends DMERTestCase
{
    /**
     * @group remoteResources
     */
    public function testDirExists(): void
    {
        $this->assertTrue(
            $this->getOneDriveService()->dirExists("/drive/root:/UNIFICATION/test")
        );
    }

    /**
     * @group remoteResources
     */
    public function testGetDirectoryForCompany(): void
    {
        $this->assertSame(
            '/drive/root:/UNIFICATION/test/TEST1',
            $this->getOneDriveService()->getDirectoryResourceForCompany($this->company)
        );
    }

    /**
     * @group remoteResources
     */
// Chris 2025.07.08 -- Commenting out because brittle
//    public function testCreateDirectoryForCompany(): void
//    {
//        try {
//            $this->getOneDriveService()->removeDir(
//                $this->getOneDriveService()->getDirectoryResourceForCompany($this->company)
//            );
//            sleep(5);
//        } catch (ClientException) {
//        }
//        $exists = $this->getOneDriveService()->dirExists(
//            $this->getOneDriveService()->getDirectoryResourceForCompany($this->company)
//        );
//        $this->assertFalse($exists);
//
//        $this->getOneDriveService()->createDirectoryResourceForCompany($this->company);
//
//        $exists = $this->getOneDriveService()->dirExists(
//            $this->getOneDriveService()->getDirectoryResourceForCompany($this->company)
//        );
//        $this->assertTrue($exists);
//
//        //Cleanup
//        $this->getOneDriveService()->removeDir(
//            $this->getOneDriveService()->getDirectoryResourceForCompany($this->company)
//        );
//        $exists = $this->getOneDriveService()->dirExists(
//            $this->getOneDriveService()->getDirectoryResourceForCompany($this->company)
//        );
//        $this->assertFalse($exists);
//    }

    /**
     * @group remoteResources
     */
    public function testDirectoryResourceExistsForCompany(): void
    {
        $result = $this->getOneDriveService()->directoryResourceExistsForCompany($this->company);
        $this->assertTrue($result);
    }

    /**
     * @group remoteResources
     */
    public function testGetAllDocumentsForCompany(): void
    {
        $result = $this->getOneDriveService()->getAllDocumentsForCompany($this->company);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * @group remoteResources
     */
// Chris 2025.07.08 -- Commenting out because brittle
//    public function testFileActions(): void
//    {
//        $result = $this->getOneDriveService()->getAllDocumentsForCompany($this->company);
//        $this->assertIsArray($result);
//        $this->assertCount(1, $result);
//
//        $this->assertTrue(
//            $this->getOneDriveService()->fileExists($this->company, 'test.xlsm')
//        );
//
//        $permissions = $this->getOneDriveService()->getFilePermissions($this->company, 'test.xlsm');
//        $this->assertIsArray($permissions);
//
//        $shareLink = $this->getOneDriveService()->getShareLink($this->company, 'test.xlsm');
//        $this->assertInstanceOf(SharingLink::class, $shareLink);
//
//        $this->getOneDriveService()->removeFile(
//            $this->company,
//            'test.xlsm'
//        );
//
//        $result = $this->getOneDriveService()->getAllDocumentsForCompany($this->company);
//        $this->assertIsArray($result);
//        $this->assertCount(0, $result);
//
//        $this->assertFalse(
//            $this->getOneDriveService()->fileExists($this->company, 'test.xlsm')
//        );
//    }
}
