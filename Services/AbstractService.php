<?php

namespace NyroDev\UtilityBundle\Services;

use NyroDev\UtilityBundle\Services\Traits\ContainerInterfaceServiceableTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\HelperInterface;

abstract class AbstractService implements HelperInterface
{
    use ContainerInterfaceServiceableTrait;

    public function getRequest(): Request
    {
        return $this->container->get('request_stack')->getCurrentRequest();
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

    public function getCharset(): string
    {
        return 'utf-8';
    }
}
