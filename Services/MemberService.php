<?php

namespace NyroDev\UtilityBundle\Services;

use NyroDev\UtilityBundle\Model\SecurityUserEntityableInterface;
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
     * @return string|Entity|Document
     */
    public function getUser()
    {
        return $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
    }

    public function getEntityUser(): mixed
    {
        $user = $this->getUser();

        if ($user && $user instanceof SecurityUserEntityableInterface) {
            return $user->getEntityUser();
        }

        return $user;
    }

    public function isLogged(): bool
    {
        return is_object($this->getUser());
    }

    public function isGranted(string $role): bool
    {
        return $this->isLogged() && $this->authorizationChecker->isGranted($role);
    }

    public function isImpersonated(): bool
    {
        return $this->isGranted('ROLE_PREVIOUS_ADMIN');
    }
}
