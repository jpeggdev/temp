<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Enum;

enum ServiceTitanConnectionStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ERROR = 'error';
    case PENDING = 'pending';
    case CONNECTED = 'connected';
}
