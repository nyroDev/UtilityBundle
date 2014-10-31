<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\AbstractType as SrcAbstractType;

abstract class AbstractType extends SrcAbstractType {
	
    /**
     * @var ContainerInterface
     */
    protected $container;
	
	public function __construct($container) {
		$this->container = $container;
	}
	
	/**
	 * Get an application parameter
	 *
	 * @param string $parameter
	 * @return mixed
	 */
	public function getParameter($parameter) {
		return $this->container->hasParameter($parameter) ? $this->container->getParameter($parameter) : null;
	}
	
	/**
     * Gets a service by id.
     *
     * @param string $id The service id
     * @return object The service
     */
	public function get($id) {
		return $this->container->get($id);
	}
	
	/**
	 * Get the translation for a given keyword
	 *
	 * @param string $key Translation key
	 * @param array $parameters Parameters to replace
	 * @param string $domain Translation domain
	 * @param string $locale Local to use
	 * @return string The translation
	 */
	public function trans($key, array $parameters = array(), $domain = 'messages', $locale = null) {
		return $this->container->get('translator')->trans($key, $parameters, $domain, $locale);
	}
	
    /**
     * Generates a URL from the given parameters.
     *
     * @param string  $route      The name of the route
     * @param mixed   $parameters An array of parameters
     * @param Boolean $absolute   Whether to generate an absolute URL
     * @return string The generated URL
     */
    public function generateUrl($route, $parameters = array(), $absolute = false) {
        return $this->container->get('nyrodev')->generateUrl($route, $parameters, $absolute);
    }
	
}