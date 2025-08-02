<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DuplicateAttendeeEmailException;

final class UniqueAttendeeEmailsValidator implements EventCheckoutValidatorInterface
{
    public function validate(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
    ): void {
        $seenEmails = [];
        foreach ($eventCheckout->getEventCheckoutAttendees() as $attendee) {
            $email = $attendee->getEmail();
            if (!$email) {
                continue;
            }

            $cleanEmail = trim($email);

            if (isset($seenEmails[$cleanEmail])) {
                throw new DuplicateAttendeeEmailException("Duplicate attendee email found: {$email}");
            }
            $seenEmails[$cleanEmail] = true;
        }
    }
}
