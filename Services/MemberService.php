<?php

namespace NyroDev\UtilityBundle\Services;

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
}
