<?php

namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class MemberService extends AbstractService
{
    /**
     * Get the logged user. Will retrun anon. If user not logged.
     *
     * @return string|Entity
     */
    public function getUser()
    {
        return $this->get('security.token_storage')->getToken() ? $this->get('security.token_storage')->getToken()->getUser() : null;
    }

    /**
     * Indicates if the user is logged or not.
     *
     * @return bool
     */
    public function isLogged()
    {
        return $this->get('security.token_storage')->getToken() && $this->get('security.token_storage')->getToken()->isAuthenticated() && is_object($this->getUser());
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
        return $this->isLogged() && $this->get('security.authorization_checker')->isGranted($role);
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

    /**
     * Log user correctly.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    public function logUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        // Here, "main" is the name of the firewall in your security.yml
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        // Fire the login event
        $this->get('event_dispatcher')->dispatch(AuthenticationEvents::AUTHENTICATION_SUCCESS, new AuthenticationEvent($token));
    }
}
