<?php

namespace App\Tests\Unit\ValueObjects;

use App\Tests\AppTestCase;

class TempFileTest extends AppTestCase
{
    public function testTempFileForUpload(): void
    {
        $fullTempFilePath =
            '/Users/chrisholland/projects/unification/var/tmp/'
            . 'PS00024/9c2846778e39dd049c56142f1236b2fde59cef89.dbf';
        $tempFile = \App\ValueObjects\TempFile::fromFullPath(
            $fullTempFilePath
        );

        self::assertSame(
            'PS00024/9c2846778e39dd049c56142f1236b2fde59cef89.dbf',
            $tempFile->getRelativePath()
        );
        self::assertSame(
            $fullTempFilePath,
            $tempFile->getFullPath()
        );
    }
}
