<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiUserProvider implements UserProviderInterface
{
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->getApiUser();
    }

    public function supportsClass(string $class): bool
    {
        return true;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->getApiUser();
    }

    private function getApiUser(): UserInterface
    {
        $apiUser = new User();
        $apiUser->setIdentifier('api-user');
        $apiUser->setAccessRoles([]);
        $apiUser->setActive(true);
        return $apiUser;
    }
}
