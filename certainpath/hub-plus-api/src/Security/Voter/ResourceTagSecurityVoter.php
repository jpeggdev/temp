<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\ResourceTag;

class ResourceTagSecurityVoter extends RoleSuperAdminVoter
{
    public const string MANAGE = 'RESOURCE_TAG_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::MANAGE === $attribute
            && ($subject instanceof ResourceTag || null === $subject);
    }
}
