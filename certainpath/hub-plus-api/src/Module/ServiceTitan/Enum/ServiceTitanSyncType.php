<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Enum;

enum ServiceTitanSyncType: string
{
    case MANUAL = 'manual';
    case SCHEDULED = 'scheduled';

    public const array VALUES = [
        self::MANUAL->value,
        self::SCHEDULED->value,
    ];
}
