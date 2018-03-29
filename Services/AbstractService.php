<?php

namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Templating\Helper\HelperInterface;

abstract class AbstractService implements HelperInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Get an application parameter.
     *
     * @param string $parameter
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter($parameter, $default = null)
    {
        $value = $this->container->hasParameter($parameter) ? $this->container->getParameter($parameter) : null;

        return !is_null($value) ? $value : $default;
    }

    /**
     * Shortcut to return the request service.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * Gets a service by id.
     *
     * @param string $id The service id
     *
     * @return object The service
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    /**
     * Get the translation for a given keyword.
     *
     * @param string $key        Translation key
     * @param array  $parameters Parameters to replace
     * @param string $domain     Translation domain
     * @param string $locale     Local to use
     *
     * @return string The translation
     */
    public function trans($key, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return $this->get('translator')->trans($key, $parameters, $domain, $locale);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $route      The name of the route
     * @param mixed  $parameters An array of parameters
     * @param bool   $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generateUrl($route, $parameters = array(), $absolute = false)
    {
        return $this->container->get('nyrodev')->generateUrl($route, $parameters, $absolute);
    }

    /**
     * Get name of the service.
     *
     * @return string
     */
    public function getName()
    {
        $tmp = explode('\\', get_class($this));

        return $tmp[count($tmp) - 1];
    }

    public function setCharset($charset)
    {
    }

    public function getCharset()
    {
        return 'utf-8';
    }
}
