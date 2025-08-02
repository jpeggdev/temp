<?php

namespace App\Tests\Entity;

use App\Entity\Address;
use App\Tests\AppTestCase;
use PHPUnit\Framework\TestCase;

class AddressTest extends AppTestCase
{
    public function testPoBox(): void
    {
        $address = new Address();
        $address->setAddress1('PO Box 123');
        self::assertTrue($address->isPoBox());

        $address->setAddress1('P.O. Box 123');
        self::assertTrue($address->isPoBox());

        $address->setAddress1('P. O.Box 123');
        self::assertTrue($address->isPoBox());

        $address->setAddress1('123 Main St');
        self::assertFalse($address->isPoBox());
    }
}
