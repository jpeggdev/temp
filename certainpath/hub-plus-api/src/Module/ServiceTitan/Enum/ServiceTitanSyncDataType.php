<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Enum;

enum ServiceTitanSyncDataType: string
{
    case INVOICES = 'invoices';
    case CUSTOMERS = 'customers';
    case BOTH = 'both';

    public const array VALUES = [
        self::INVOICES->value,
        self::CUSTOMERS->value,
        self::BOTH->value,
    ];
}
