<?php

declare(strict_types=1);

namespace App\Enum;

enum AuditLogOperation: string
{
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
}
