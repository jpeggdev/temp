<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Voter;

use App\Entity\EventVenue;
use App\Security\Voter\RoleSuperAdminVoter;

class EventVenueVoter extends RoleSuperAdminVoter
{
    public const string CREATE = 'EVENT_VENUE_CREATE';
    public const string READ = 'EVENT_VENUE_READ';
    public const string UPDATE = 'EVENT_VENUE_UPDATE';
    public const string DELETE = 'EVENT_VENUE_DELETE';
    public const string DUPLICATE = 'EVENT_VENUE_DUPLICATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $allowedAttributes = [
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DUPLICATE,
            self::DELETE,
        ];

        $isValidSubject = $subject instanceof EventVenue || null === $subject;

        return in_array($attribute, $allowedAttributes, true) && $isValidSubject;
    }
}
