<?php

namespace NyroDev\UtilityBundle\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SrcAbstractController;

abstract class AbstractController extends SrcAbstractController implements ContainerInterface
{
    use Traits\SubscribedServiceTrait;

    public function get(string $id): mixed
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    protected function trans(string $key, array $parameters = [], string $domain = 'messages', ?string $locale = null): string
    {
        return $this->get('translator')->trans($key, $parameters, $domain, $locale);
    }
}
