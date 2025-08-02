<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class NotEnoughSeatsAvailableException extends \RuntimeException
{
    /**
     * @param int $seatsAvailable The number of remaining seats
     */
    public function __construct(
        int $seatsAvailable,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $message = sprintf(
            'Not enough seats are available for this session. Only %d remaining.',
            $seatsAvailable
        );

        parent::__construct($message, $code, $previous);
    }
}
