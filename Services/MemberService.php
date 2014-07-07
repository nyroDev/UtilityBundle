<?php
namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
	Symfony\Component\Security\Core\AuthenticationEvents,
	Symfony\Component\Security\Core\Event\AuthenticationEvent;

class MemberService extends AbstractService {

	
	/**
	 * Get the logged user. Will retrun anon. If user not logged
	 *
	 * @return string|Entity
	 */
	public function getUser() {
		return $this->get('security.context')->getToken()->getUser();
	}
	
	/**
	 * Indicates if the user is logged or not
	 *
	 * @return boolean
	 */
	public function isLogged() {
		return $this->get('security.context')->getToken()->isAuthenticated() && is_object($this->getUser());
	}
	
	/**
	 * Indicates if the logged user is impersonated
	 *
	 * @return boolean
	 */
	public function isImpersonated() {
		return $this->isLogged() && $this->get('security.context')->isGranted('ROLE_PREVIOUS_ADMIN');
	}
	
	public function logUser(\Symfony\Component\Security\Core\User\UserInterface $user) {
		// Here, "main" is the name of the firewall in your security.yml
		$token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
		$this->get('security.context')->setToken($token);

		// Fire the login event
		$this->get('event_dispatcher')->dispatch(AuthenticationEvents::AUTHENTICATION_SUCCESS, new AuthenticationEvent($token));
	}

}

