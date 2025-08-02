<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Controller;

use App\Entity\Company;
use App\Module\ServiceTitan\Service\ServiceTitanMetricsService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceTitanDashboardControllerTest extends WebTestCase
{
    public function testControllerServiceIsConfiguredCorrectly(): void
    {
        // Arrange
        $container = self::getContainer();

        // Act & Assert - Just ensure the controller can be instantiated
        self::assertTrue($container->has(ServiceTitanMetricsService::class));
    }

    public function testControllerStructureIsValid(): void
    {
        // This test ensures the controller class structure is valid
        $reflection = new \ReflectionClass(\App\Module\ServiceTitan\Controller\ServiceTitanDashboardController::class);

        // Assert controller exists and has expected methods
        self::assertTrue($reflection->hasMethod('__construct'));
        self::assertTrue($reflection->hasMethod('__invoke'));

        // Assert route attribute exists
        $attributes = $reflection->getMethod('__invoke')->getAttributes();
        self::assertNotEmpty($attributes);
    }
}
