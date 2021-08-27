<?php

namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MemberService extends AbstractService
{

    private $tokenStorage;
    private $authorizationChecker;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
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
     *
     * @return bool
     */
    public function isLogged()
    {
        return $this->tokenStorage->getToken() && $this->tokenStorage->getToken()->isAuthenticated() && is_object($this->getUser());
    }

    /**
     * Check if the user is logged and granted with the given role.
     *
     * @param string $role Role name
     *
     * @return bool
     */
    public function isGranted($role)
    {
        return $this->isLogged() && $this->authorizationChecker->isGranted($role);
    }

    /**
     * Indicates if the logged user is impersonated.
     *
     * @return bool
     */
    public function isImpersonated()
    {
        return $this->isGranted('ROLE_PREVIOUS_ADMIN');
    }
}
