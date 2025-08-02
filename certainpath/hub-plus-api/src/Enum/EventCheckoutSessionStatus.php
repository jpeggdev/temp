<?php

namespace App\Enum;

enum EventCheckoutSessionStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
    case CANCELED = 'canceled';

    public const array VALUES = [
        self::IN_PROGRESS->value,
        self::COMPLETED->value,
        self::EXPIRED->value,
        self::CANCELED->value,
    ];
}
