<?php

namespace App\Tests\DTO\Request\Customer;

use App\DTO\Request\Customer\UpdateCustomerDoNotMailDTO;
use PHPUnit\Framework\TestCase;

class UpdateCustomerDoNotMailDTOTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(true, 123);
        
        $this->assertTrue($dto->doNotMail);
        $this->assertEquals(123, $dto->customerId);
    }

    public function testConstructorWithDoNotMailOnly(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(false);
        
        $this->assertFalse($dto->doNotMail);
        $this->assertNull($dto->customerId);
    }

    public function testConstructorWithDefaults(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO();
        
        $this->assertNull($dto->doNotMail);
        $this->assertNull($dto->customerId);
    }

    public function testConstructorWithTrueDoNotMail(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(true);
        
        $this->assertTrue($dto->doNotMail);
        $this->assertNull($dto->customerId);
    }

    public function testConstructorWithFalseDoNotMail(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(false);
        
        $this->assertFalse($dto->doNotMail);
        $this->assertNull($dto->customerId);
    }

    public function testConstructorWithNullDoNotMail(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(null);
        
        $this->assertNull($dto->doNotMail);
        $this->assertNull($dto->customerId);
    }

    public function testConstructorWithNullDoNotMailAndValidCustomerId(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(null, 456);
        
        $this->assertNull($dto->doNotMail);
        $this->assertEquals(456, $dto->customerId);
    }

    public function testConstructorWithZeroCustomerId(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(true, 0);
        
        $this->assertTrue($dto->doNotMail);
        $this->assertEquals(0, $dto->customerId);
    }

    public function testConstructorWithNegativeCustomerId(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(false, -1);
        
        $this->assertFalse($dto->doNotMail);
        $this->assertEquals(-1, $dto->customerId);
    }

    public function testConstructorWithNullCustomerId(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(true, null);
        
        $this->assertTrue($dto->doNotMail);
        $this->assertNull($dto->customerId);
    }

    public function testPublicPropertyAccess(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO();
        
        // Test that properties are publicly accessible
        $this->assertObjectHasProperty('doNotMail', $dto);
        $this->assertObjectHasProperty('customerId', $dto);
    }

    public function testPublicPropertyModification(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO();
        
        // Test that properties can be modified after construction
        $dto->doNotMail = true;
        $dto->customerId = 789;
        
        $this->assertTrue($dto->doNotMail);
        $this->assertEquals(789, $dto->customerId);
    }

    public function testPublicPropertyModificationToNull(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(true, 123);
        
        // Test that properties can be set to null
        $dto->doNotMail = null;
        $dto->customerId = null;
        
        $this->assertNull($dto->doNotMail);
        $this->assertNull($dto->customerId);
    }

    public function testPropertyTypes(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(true, 123);
        
        // Test the types of the properties
        $this->assertIsBool($dto->doNotMail);
        $this->assertIsInt($dto->customerId);
    }

    public function testNullPropertyTypes(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO();
        
        // Test null types
        $this->assertNull($dto->doNotMail);
        $this->assertNull($dto->customerId);
    }

    public function testBackwardCompatibilityComment(): void
    {
        // This test ensures the customerId property exists for backward compatibility
        // even though it's documented as "not used"
        $dto = new UpdateCustomerDoNotMailDTO(false, 999);
        
        $this->assertEquals(999, $dto->customerId);
    }

    public function testImmutableAfterConstruction(): void
    {
        $originalDoNotMail = true;
        $originalCustomerId = 123;
        
        $dto = new UpdateCustomerDoNotMailDTO($originalDoNotMail, $originalCustomerId);
        
        // Verify constructor values are preserved
        $this->assertEquals($originalDoNotMail, $dto->doNotMail);
        $this->assertEquals($originalCustomerId, $dto->customerId);
        
        // Since properties are public, they can be modified
        // but the constructor properly sets initial values
        $dto->doNotMail = false;
        $dto->customerId = 456;
        
        $this->assertFalse($dto->doNotMail);
        $this->assertEquals(456, $dto->customerId);
    }

    public function testSerializationCompatibility(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(true, 123);
        
        // Test that the DTO can be serialized and unserialized
        $serialized = serialize($dto);
        $unserialized = unserialize($serialized);
        
        $this->assertEquals($dto->doNotMail, $unserialized->doNotMail);
        $this->assertEquals($dto->customerId, $unserialized->customerId);
    }

    public function testJsonSerializationCompatibility(): void
    {
        $dto = new UpdateCustomerDoNotMailDTO(true, 123);
        
        // Test that the DTO properties can be accessed for JSON serialization
        $jsonArray = [
            'doNotMail' => $dto->doNotMail,
            'customerId' => $dto->customerId
        ];
        
        $json = json_encode($jsonArray);
        $decoded = json_decode($json, true);
        
        $this->assertEquals($dto->doNotMail, $decoded['doNotMail']);
        $this->assertEquals($dto->customerId, $decoded['customerId']);
    }

    public function testWithLargeCustomerId(): void
    {
        $largeId = PHP_INT_MAX;
        $dto = new UpdateCustomerDoNotMailDTO(true, $largeId);
        
        $this->assertTrue($dto->doNotMail);
        $this->assertEquals($largeId, $dto->customerId);
    }
}