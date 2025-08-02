<?php

namespace App\Tests\Unit\ValueObjects;

use App\Tests\AppTestCase;
use App\ValueObjects\SubscriptionObject;
use DateTimeInterface;

class SubscriptionObjectTest extends AppTestCase
{
    private SubscriptionObject $subscriptionObject;

    public function setUp(): void
    {
        $this->subscriptionObject = new SubscriptionObject([
            'customerId' => '123',
            'companyId' => '456',
            'name' => 'Test Subscription',
            'frequency' => 'monthly',
            'price' => '99.99',
            'startsAt' => date_create('2024-01-01'),
            'endsAt' => date_create('2024-12-31')
        ]);
    }

    public function testConstructor(): void
    {
        $valueObject = new SubscriptionObject([]);
        $this->assertJson($valueObject->toJson());
    }

    public function testIsValid(): void
    {
        $this->assertTrue($this->subscriptionObject->isValid());

        $invalidSubscription = new SubscriptionObject([]);
        $this->assertFalse($invalidSubscription->isValid());
    }

    public function testToArray(): void
    {
        $array = $this->subscriptionObject->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('123', $array['customer_id']);
        $this->assertEquals('Test Subscription', $array['name']);
        $this->assertTrue($array['is_active']);
        $this->assertFalse($array['is_deleted']);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    public function testPopulate(): void
    {
        $subscription = $this->subscriptionObject->populate();

        $this->assertStringContainsString('testsubscription', $subscription->key);
        $this->assertStringContainsString('monthly', $subscription->key);
        $this->assertStringContainsString('9999', $subscription->key);
        $this->assertStringContainsString('testsubscriptionmonthly9999', $subscription->key);
    }

    public function testDefaultPropertyValues(): void
    {
        $subscription = new SubscriptionObject([]);

        // Test string properties default to null
        $this->assertIsNumeric($subscription->companyId);
        $this->assertNull($subscription->customerId);
        $this->assertNull($subscription->name);
        $this->assertNull($subscription->frequency);
        $this->assertNull($subscription->price);
        $this->assertNull($subscription->startsAt);
        $this->assertNull($subscription->endsAt);

        // Test inherited properties from AbstractObject
        $this->assertEquals(0, $subscription->_id);
        $this->assertEquals('', $subscription->key);
        $this->assertEquals([], $subscription->_extra);
        $this->assertInstanceOf(DateTimeInterface::class, $subscription->imported);
        $this->assertInstanceOf(DateTimeInterface::class, $subscription->createdAt);
        $this->assertInstanceOf(DateTimeInterface::class, $subscription->updatedAt);
        $this->assertTrue($subscription->isActive);
        $this->assertFalse($subscription->isDeleted);
    }
}
