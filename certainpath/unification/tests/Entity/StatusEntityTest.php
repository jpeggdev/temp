<?php

namespace App\Tests\Entity;

use App\Entity\Address;
use App\Tests\AppTestCase;

class StatusEntityTest extends AppTestCase
{
    public function testIsDeleted(): void
    {
        $address = new Address();
        self::assertFalse($address->isDeleted());

        $address->setDeleted(true);
        self::assertTrue($address->isDeleted());
        self::assertInstanceOf(\DateTimeImmutable::class, $address->getDeletedAt());

        $address->setDeleted(false);
        self::assertFalse($address->isDeleted());
        self::assertNull($address->getDeletedAt());

        $address->setDeletedAt(date_create_immutable());
        self::assertTrue($address->isDeleted());
        self::assertInstanceOf(\DateTimeImmutable::class, $address->getDeletedAt());

        $address->setDeletedAt(null);
        self::assertFalse($address->isDeleted());
        self::assertNull($address->getDeletedAt());
    }
}
