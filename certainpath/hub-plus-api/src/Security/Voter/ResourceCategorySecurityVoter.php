<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\ResourceCategory;

class ResourceCategorySecurityVoter extends RoleSuperAdminVoter
{
    public const string MANAGE = 'RESOURCE_CATEGORY_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::MANAGE === $attribute
            && ($subject instanceof ResourceCategory || null === $subject);
    }
}
