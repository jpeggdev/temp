<?php

namespace App\Exceptions\InvalidArgumentException;

use InvalidArgumentException;

class InvalidMailingDropWeekException extends InvalidArgumentException
{
    public function __construct(int $mailingDropWeek, int $mailingFrequencyWeeks)
    {
        $messageTemplate = 'The provided mailing drop week value "%d" is invalid. The maximum allowed value is %d';
        $message = sprintf($messageTemplate, $mailingDropWeek, $mailingFrequencyWeeks);

        parent::__construct($message);
    }
}
