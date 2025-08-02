<?php

declare(strict_types=1);

namespace App\Enum;

enum CreditMemoType: string
{
    case VOUCHER = 'voucher';

    public const array VALUES = [
        self::VOUCHER->value,
    ];
}
