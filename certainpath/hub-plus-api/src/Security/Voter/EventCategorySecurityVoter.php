<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\EventCategory;

class EventCategorySecurityVoter extends RoleSuperAdminVoter
{
    public const string EVENT_CATEGORY_MANAGE = 'EVENT_CATEGORY_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::EVENT_CATEGORY_MANAGE === $attribute
            && ($subject instanceof EventCategory || null === $subject);
    }
}
