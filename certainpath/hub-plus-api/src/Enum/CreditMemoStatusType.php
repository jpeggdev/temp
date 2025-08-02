<?php

declare(strict_types=1);

namespace App\Enum;

enum CreditMemoStatusType: string
{
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case APPLIED = 'applied';

    public const array VALUES = [
        self::DRAFT->value,
        self::POSTED->value,
        self::APPLIED->value,
    ];
}
