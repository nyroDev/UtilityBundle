<?php

namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MemberService extends AbstractService
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /**
     * Get the logged user. Will retrun anon. If user not logged.
     *
     * @return string|Entity
     */
    public function getUser()
    {
        return $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
    }

    /**
     * Indicates if the user is logged or not.
     */
    public function isLogged(): bool
    {
        return is_object($this->getUser());
    }

    /**
     * Check if the user is logged and granted with the given role.
     */
    public function isGranted(string $role): bool
    {
        return $this->isLogged() && $this->authorizationChecker->isGranted($role);
    }

    /**
     * Indicates if the logged user is impersonated.
     */
    public function isImpersonated(): bool
    {
        return $this->isGranted('ROLE_PREVIOUS_ADMIN');
    }
}
