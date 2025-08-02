<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class AttendeeAlreadyEnrolledException extends \RuntimeException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Attendee with email %s is already enrolled in this session.', $email));
    }
}
