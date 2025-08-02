<?php

declare(strict_types=1);

namespace App\Security\Voter;

class CaraApiVoter extends RoleSuperAdminVoter
{
    public const string CARA_API = 'CARA_API_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::CARA_API === $attribute;
    }
}
