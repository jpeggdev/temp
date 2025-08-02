<?php

namespace App\Tests\ValueObject;

use App\Tests\AbstractKernelTestCase;
use App\ValueObject\FileHash;

class FileHashTest extends AbstractKernelTestCase
{
    public function testFromFileSystem(): void
    {
        $testFilePath = './tests/Files/ACXIOM 110 prospects.csv';
        self::assertFileExists(
            $testFilePath
        );
        $referenceFileHash = '6231b420b60db4f5dcf12c69a473e955';

        $fileHash = FileHash::fromFileSystem($testFilePath);
        self::assertSame($referenceFileHash, $fileHash->getString());
    }
}
