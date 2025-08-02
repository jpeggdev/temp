<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Enum;

enum ServiceTitanDataType: string
{
    case INVOICES = 'invoices';
    case CUSTOMERS = 'customers';
    case BOTH = 'both';
}
