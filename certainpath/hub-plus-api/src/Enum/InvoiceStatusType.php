<?php

declare(strict_types=1);

namespace App\Enum;

enum InvoiceStatusType: string
{
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case PAID = 'paid';
    case REFUNDED = 'refunded';

    public const array VALUES = [
        self::DRAFT->value,
        self::POSTED->value,
        self::PAID->value,
        self::REFUNDED->value,
    ];
}
