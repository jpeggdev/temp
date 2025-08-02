<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Enum;

enum ServiceTitanSyncStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
