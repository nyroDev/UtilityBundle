<?php

namespace NyroDev\UtilityBundle\Controller;

use NyroDev\UtilityBundle\Services\NyrodevService;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;

abstract class AbstractController
{
    use ControllerTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @required
     */
    public function setContainer(ContainerInterface $container)
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }

    protected function getParameter($name, $default = null)
    {
        return $this->container->get(NyrodevService::class)->getParameter($name, $default);
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
    protected function trans($key, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        return $this->get('translator')->trans($key, $parameters, $domain, $locale);
    }
}
