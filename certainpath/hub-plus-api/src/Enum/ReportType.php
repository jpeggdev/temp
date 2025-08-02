<?php

declare(strict_types=1);

namespace App\Enum;

enum ReportType: string
{
    case MONTHLY_BALANCE_SHEET = 'monthly_balance_sheet';
    case PROFIT_AND_LOSS = 'profit_and_loss';
    case TRANSACTION_LIST = 'transaction_list';

    public const array VALUES = [
        self::MONTHLY_BALANCE_SHEET->value,
        self::PROFIT_AND_LOSS->value,
        self::TRANSACTION_LIST->value,
    ];
}
