<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Psr\Container\ContainerInterface;

trait ContainerInterfaceServiceableTrait
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Required
     */
    public function setContainerInterface(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getContainerInterface(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Check if an application parameter is set.
     *
     * @param string $parameter
     *
     * @return bool
     */
    public function hasParameter($parameter)
    {
        return $this->container->hasParameter($parameter);
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
        $value = $this->hasParameter($parameter) ? $this->container->getParameter($parameter) : null;

        return !is_null($value) ? $value : $default;
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
     * Get the translation for a given keyword.
     *
     * @param string $key        Translation key
     * @param array  $parameters Parameters to replace
     * @param string $domain     Translation domain
     * @param string $locale     Local to use
     *
     * @return string The translation
     */
    public function trans($key, array $parameters = [], $domain = 'messages', $locale = null)
    {
        return $this->container->get('translator')->trans($key, $parameters, $domain, $locale);
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
    public function generateUrl($route, $parameters = [], $absolute = false)
    {
        return $this->container->get(NyrodevService::class)->generateUrl($route, $parameters, $absolute);
    }
}
