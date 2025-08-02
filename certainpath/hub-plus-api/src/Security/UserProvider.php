<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
class UserProvider implements UserProviderInterface
{
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    // region loadUserByIdentifier
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        $impersonateUserUuid = $request?->headers->get('X-Impersonate-User-UUID');

        if ($impersonateUserUuid) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $impersonateUserUuid]);

            return !$user ? throw new UserNotFoundException("Impersonated user with UUID \"{$impersonateUserUuid}\" not found.") : $user;
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['ssoId' => $identifier]);

        return !$user ? throw new UserNotFoundException("User with SSO ID \"{$identifier}\" not found.") : $user;
    }
    // endregion

    // region refreshUser
    public function refreshUser(UserInterface $user): UserInterface
    {
        return !$user instanceof User ? throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user))) : $this->loadUserByIdentifier($user->getSsoId());
    }
    // endregion

    // region supportsClass
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
    // endregion
}
