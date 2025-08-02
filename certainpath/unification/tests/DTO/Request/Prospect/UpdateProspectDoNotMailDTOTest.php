<?php

namespace App\Tests\DTO\Request\Prospect;

use App\DTO\Request\Prospect\UpdateProspectDoNotMailDTO;
use PHPUnit\Framework\TestCase;

class UpdateProspectDoNotMailDTOTest extends TestCase
{
    public function testConstructorWithTrueParameter(): void
    {
        $dto = new UpdateProspectDoNotMailDTO(true);
        
        $this->assertTrue($dto->doNotMail);
    }

    public function testConstructorWithFalseParameter(): void
    {
        $dto = new UpdateProspectDoNotMailDTO(false);
        
        $this->assertFalse($dto->doNotMail);
    }

    public function testConstructorWithNullParameter(): void
    {
        $dto = new UpdateProspectDoNotMailDTO(null);
        
        $this->assertNull($dto->doNotMail);
    }

    public function testConstructorWithDefaultParameter(): void
    {
        $dto = new UpdateProspectDoNotMailDTO();
        
        $this->assertNull($dto->doNotMail);
    }

    public function testFromBoolWithTrue(): void
    {
        $dto = UpdateProspectDoNotMailDTO::fromBool(true);
        
        $this->assertTrue($dto->doNotMail);
        $this->assertInstanceOf(UpdateProspectDoNotMailDTO::class, $dto);
    }

    public function testFromBoolWithFalse(): void
    {
        $dto = UpdateProspectDoNotMailDTO::fromBool(false);
        
        $this->assertFalse($dto->doNotMail);
        $this->assertInstanceOf(UpdateProspectDoNotMailDTO::class, $dto);
    }

    public function testFromBoolWithNull(): void
    {
        $dto = UpdateProspectDoNotMailDTO::fromBool(null);
        
        $this->assertNull($dto->doNotMail);
        $this->assertInstanceOf(UpdateProspectDoNotMailDTO::class, $dto);
    }

    public function testPublicPropertyAccess(): void
    {
        $dto = new UpdateProspectDoNotMailDTO();
        
        // Test that property is publicly accessible
        $this->assertObjectHasProperty('doNotMail', $dto);
    }

    public function testPublicPropertyModification(): void
    {
        $dto = new UpdateProspectDoNotMailDTO();
        
        // Test that property can be modified after construction
        $dto->doNotMail = true;
        $this->assertTrue($dto->doNotMail);
        
        $dto->doNotMail = false;
        $this->assertFalse($dto->doNotMail);
        
        $dto->doNotMail = null;
        $this->assertNull($dto->doNotMail);
    }

    public function testPropertyType(): void
    {
        $dto = new UpdateProspectDoNotMailDTO(true);
        
        // Test the type of the property
        $this->assertIsBool($dto->doNotMail);
    }

    public function testNullPropertyType(): void
    {
        $dto = new UpdateProspectDoNotMailDTO();
        
        // Test null type
        $this->assertNull($dto->doNotMail);
    }

    public function testStaticFactoryMethodReturnsSelf(): void
    {
        $dto = UpdateProspectDoNotMailDTO::fromBool(true);
        
        $this->assertInstanceOf(UpdateProspectDoNotMailDTO::class, $dto);
        $this->assertEquals('self', (new \ReflectionMethod(UpdateProspectDoNotMailDTO::class, 'fromBool'))->getReturnType()->getName());
    }

    public function testFactoryMethodEquivalentToConstructor(): void
    {
        $constructorDto = new UpdateProspectDoNotMailDTO(true);
        $factoryDto = UpdateProspectDoNotMailDTO::fromBool(true);
        
        $this->assertEquals($constructorDto->doNotMail, $factoryDto->doNotMail);
    }

    public function testFactoryMethodWithNullEquivalentToConstructor(): void
    {
        $constructorDto = new UpdateProspectDoNotMailDTO(null);
        $factoryDto = UpdateProspectDoNotMailDTO::fromBool(null);
        
        $this->assertEquals($constructorDto->doNotMail, $factoryDto->doNotMail);
    }

    public function testFactoryMethodWithFalseEquivalentToConstructor(): void
    {
        $constructorDto = new UpdateProspectDoNotMailDTO(false);
        $factoryDto = UpdateProspectDoNotMailDTO::fromBool(false);
        
        $this->assertEquals($constructorDto->doNotMail, $factoryDto->doNotMail);
    }

    public function testImmutableAfterConstruction(): void
    {
        $originalValue = true;
        $dto = new UpdateProspectDoNotMailDTO($originalValue);
        
        // Verify constructor value is preserved
        $this->assertEquals($originalValue, $dto->doNotMail);
        
        // Since property is public, it can be modified
        // but the constructor properly sets initial value
        $dto->doNotMail = false;
        $this->assertFalse($dto->doNotMail);
    }

    public function testSerializationCompatibility(): void
    {
        $dto = new UpdateProspectDoNotMailDTO(true);
        
        // Test that the DTO can be serialized and unserialized
        $serialized = serialize($dto);
        $unserialized = unserialize($serialized);
        
        $this->assertEquals($dto->doNotMail, $unserialized->doNotMail);
    }

    public function testJsonSerializationCompatibility(): void
    {
        $dto = new UpdateProspectDoNotMailDTO(true);
        
        // Test that the DTO property can be accessed for JSON serialization
        $jsonArray = [
            'doNotMail' => $dto->doNotMail
        ];
        
        $json = json_encode($jsonArray);
        $decoded = json_decode($json, true);
        
        $this->assertEquals($dto->doNotMail, $decoded['doNotMail']);
    }

    public function testFactoryMethodChaining(): void
    {
        // Test that the factory method returns an instance that can be used immediately
        $result = UpdateProspectDoNotMailDTO::fromBool(true)->doNotMail;
        
        $this->assertTrue($result);
    }

    public function testMultipleInstances(): void
    {
        $dto1 = new UpdateProspectDoNotMailDTO(true);
        $dto2 = new UpdateProspectDoNotMailDTO(false);
        $dto3 = UpdateProspectDoNotMailDTO::fromBool(null);
        
        $this->assertTrue($dto1->doNotMail);
        $this->assertFalse($dto2->doNotMail);
        $this->assertNull($dto3->doNotMail);
        
        // Ensure instances are independent
        $this->assertNotSame($dto1, $dto2);
        $this->assertNotSame($dto2, $dto3);
        $this->assertNotSame($dto1, $dto3);
    }

    public function testFactoryMethodCreatesNewInstance(): void
    {
        $dto1 = UpdateProspectDoNotMailDTO::fromBool(true);
        $dto2 = UpdateProspectDoNotMailDTO::fromBool(true);
        
        // Ensure each factory call creates a new instance
        $this->assertNotSame($dto1, $dto2);
        $this->assertEquals($dto1->doNotMail, $dto2->doNotMail);
    }

    public function testConstructorAndFactoryCreateIndependentInstances(): void
    {
        $constructorDto = new UpdateProspectDoNotMailDTO(true);
        $factoryDto = UpdateProspectDoNotMailDTO::fromBool(true);
        
        // Ensure they are different instances
        $this->assertNotSame($constructorDto, $factoryDto);
        
        // But have the same data
        $this->assertEquals($constructorDto->doNotMail, $factoryDto->doNotMail);
        
        // Modifying one doesn't affect the other
        $constructorDto->doNotMail = false;
        $this->assertTrue($factoryDto->doNotMail);
    }

    public function testStaticMethodAccessibility(): void
    {
        // Test that fromBool is a static method and can be called on the class
        $reflection = new \ReflectionMethod(UpdateProspectDoNotMailDTO::class, 'fromBool');
        
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function testOnlyOneProperty(): void
    {
        $dto = new UpdateProspectDoNotMailDTO(true);
        $reflection = new \ReflectionClass($dto);
        $properties = $reflection->getProperties();
        
        // Ensure DTO only has the doNotMail property
        $this->assertCount(1, $properties);
        $this->assertEquals('doNotMail', $properties[0]->getName());
    }

    public function testConstructorParameterIsOptional(): void
    {
        // Test that constructor parameter has a default value
        $reflection = new \ReflectionMethod(UpdateProspectDoNotMailDTO::class, '__construct');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertTrue($parameters[0]->isOptional());
        $this->assertNull($parameters[0]->getDefaultValue());
    }
}