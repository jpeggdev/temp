<?php

namespace App\Tests\ValueObject;

use App\Tests\AbstractKernelTestCase;
use App\ValueObject\NormalizedField;

class NormalizedFieldTest extends AbstractKernelTestCase
{
    public function testNormalizedFieldName(): void
    {
        $normalizedField = NormalizedField::fromString(
            ' ziP    CoDE '
        );
        self::assertSame(
            'zip_code',
            $normalizedField->getValue()
        );

        self::assertSame(
            'phone',
            NormalizedField::fromString(
                '   PHONE  '
            )->getValue()
        );
    }
}
