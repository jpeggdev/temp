<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Entity\User;
use Auth0\Symfony\Security\UserProvider as Auth0UserProvider;
use Auth0\Symfony\Contracts\Security\UserProviderInterface;
use Auth0\Symfony\Models\User as Auth0User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\{
    UserInterface as SymfonyUserInterface,
    UserProviderInterface as SymfonyUserProviderInterface
};

/**
 * @template-implements SymfonyUserProviderInterface<SymfonyUserInterface>
 */
final class UserProvider implements SymfonyUserProviderInterface, UserProviderInterface
{
    public function __construct(
        private readonly Auth0UserProvider $auth0Provider,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function loadByUserModel(Auth0User $user): SymfonyUserInterface
    {
        return $user;
    }

    public function loadUserByIdentifier(string $identifier): SymfonyUserInterface
    {
        if ($user = $this->userRepository->findOneByIdentifier($identifier)) {
            return $user;
        }

        $auth0User = $this->auth0Provider->loadUserByIdentifier($identifier);
        if (
            $user = $this->userRepository->findOneByIdentifier($auth0User->getEmail())
        ) {
            foreach ($auth0User->getRoles() as $role) {
                $user->addRole($role);
            }

            return $user;
        }


        throw new UserNotFoundException();
    }

    public function refreshUser(SymfonyUserInterface $user): SymfonyUserInterface
    {
        return $user;
    }

    public function supportsClass($class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
