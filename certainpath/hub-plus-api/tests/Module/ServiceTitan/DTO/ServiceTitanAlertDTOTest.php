<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\DTO;

use App\Module\ServiceTitan\DTO\ServiceTitanAlertDTO;
use PHPUnit\Framework\TestCase;

class ServiceTitanAlertDTOTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        // Arrange
        $id = 'test-alert-id';
        $type = ServiceTitanAlertDTO::TYPE_CONNECTION;
        $severity = ServiceTitanAlertDTO::SEVERITY_WARNING;
        $title = 'Test Alert';
        $message = 'This is a test alert message';
        $createdAt = new \DateTime();
        $metadata = ['environment' => 'production'];
        $actionUrl = '/test-action';
        $actionLabel = 'Fix Now';

        // Act
        $dto = new ServiceTitanAlertDTO(
            $id,
            $type,
            $severity,
            $title,
            $message,
            $createdAt,
            $metadata,
            $actionUrl,
            $actionLabel
        );

        // Assert
        self::assertSame($id, $dto->getId());
        self::assertSame($type, $dto->getType());
        self::assertSame($severity, $dto->getSeverity());
        self::assertSame($title, $dto->getTitle());
        self::assertSame($message, $dto->getMessage());
        self::assertSame($createdAt, $dto->getCreatedAt());
        self::assertSame($metadata, $dto->getMetadata());
        self::assertSame($actionUrl, $dto->getActionUrl());
        self::assertSame($actionLabel, $dto->getActionLabel());
    }

    public function testConstructorWithOptionalParametersNull(): void
    {
        // Arrange
        $id = 'test-alert-id';
        $type = ServiceTitanAlertDTO::TYPE_SYNC;
        $severity = ServiceTitanAlertDTO::SEVERITY_ERROR;
        $title = 'Error Alert';
        $message = 'Something went wrong';
        $createdAt = new \DateTime();

        // Act
        $dto = new ServiceTitanAlertDTO(
            $id,
            $type,
            $severity,
            $title,
            $message,
            $createdAt
        );

        // Assert
        self::assertNull($dto->getMetadata());
        self::assertNull($dto->getActionUrl());
        self::assertNull($dto->getActionLabel());
    }

    public function testJsonSerialize(): void
    {
        // Arrange
        $createdAt = new \DateTime('2023-01-01 12:00:00');

        $dto = new ServiceTitanAlertDTO(
            'alert-123',
            ServiceTitanAlertDTO::TYPE_TOKEN,
            ServiceTitanAlertDTO::SEVERITY_INFO,
            'Token Refresh',
            'Token has been refreshed successfully',
            $createdAt,
            ['environment' => 'sandbox'],
            '/tokens/refresh',
            'View Details'
        );

        // Act
        $result = $dto->jsonSerialize();

        // Assert
        $expected = [
            'id' => 'alert-123',
            'type' => ServiceTitanAlertDTO::TYPE_TOKEN,
            'severity' => ServiceTitanAlertDTO::SEVERITY_INFO,
            'title' => 'Token Refresh',
            'message' => 'Token has been refreshed successfully',
            'createdAt' => '2023-01-01T12:00:00+00:00',
            'metadata' => ['environment' => 'sandbox'],
            'actionUrl' => '/tokens/refresh',
            'actionLabel' => 'View Details',
        ];

        self::assertSame($expected, $result);
    }

    public function testConstants(): void
    {
        // Test severity constants
        self::assertSame('info', ServiceTitanAlertDTO::SEVERITY_INFO);
        self::assertSame('warning', ServiceTitanAlertDTO::SEVERITY_WARNING);
        self::assertSame('error', ServiceTitanAlertDTO::SEVERITY_ERROR);
        self::assertSame('success', ServiceTitanAlertDTO::SEVERITY_SUCCESS);

        // Test type constants
        self::assertSame('connection', ServiceTitanAlertDTO::TYPE_CONNECTION);
        self::assertSame('sync', ServiceTitanAlertDTO::TYPE_SYNC);
        self::assertSame('token', ServiceTitanAlertDTO::TYPE_TOKEN);
        self::assertSame('system', ServiceTitanAlertDTO::TYPE_SYSTEM);
    }
}
