<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Enum;

enum ServiceTitanEnvironment: string
{
    case INTEGRATION = 'integration';
    case PRODUCTION = 'production';

    public const array VALUES = [
        self::INTEGRATION->value,
        self::PRODUCTION->value,
    ];
}
