<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\Voter;

use App\Entity\EventDiscount;
use App\Security\Voter\RoleSuperAdminVoter;

class EventDiscountVoter extends RoleSuperAdminVoter
{
    public const string CREATE = 'EVENT_DISCOUNT_CREATE';
    public const string READ = 'EVENT_DISCOUNT_READ';
    public const string UPDATE = 'EVENT_DISCOUNT_UPDATE';
    public const string DELETE = 'EVENT_DISCOUNT_DELETE';
    public const string DUPLICATE = 'EVENT_DISCOUNT_DUPLICATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $allowedAttributes = [
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DUPLICATE,
            self::DELETE,
        ];

        $isValidSubject = $subject instanceof EventDiscount || null === $subject;

        return in_array($attribute, $allowedAttributes, true) && $isValidSubject;
    }
}
