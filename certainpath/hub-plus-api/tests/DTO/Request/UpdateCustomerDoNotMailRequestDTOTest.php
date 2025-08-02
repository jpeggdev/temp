<?php

namespace App\Tests\DTO\Request;

use App\DTO\Request\Customer\UpdateStochasticCustomerDoNotMailRequestDTO;
use PHPUnit\Framework\TestCase;

class UpdateCustomerDoNotMailRequestDTOTest extends TestCase
{
    public function testConstructorWithTrue(): void
    {
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        $this->assertTrue($dto->doNotMail);
    }

    public function testConstructorWithFalse(): void
    {
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(false);

        $this->assertFalse($dto->doNotMail);
    }

    public function testPropertyIsPublic(): void
    {
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        // Test that the property can be accessed directly
        $this->assertTrue($dto->doNotMail);
    }

    public function testPropertyTypeIsBool(): void
    {
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        $reflection = new \ReflectionClass($dto);
        $property = $reflection->getProperty('doNotMail');
        $type = $property->getType();

        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertEquals('bool', $type->getName());
        $this->assertFalse($type->allowsNull());
    }

    /**
     * @dataProvider booleanProvider
     */
    public function testWithDifferentBooleanValues(bool $value): void
    {
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO($value);

        $this->assertEquals($value, $dto->doNotMail);
        $this->assertIsBool($dto->doNotMail);
    }

    public function booleanProvider(): array
    {
        return [
            'true value' => [true],
            'false value' => [false],
        ];
    }
}
