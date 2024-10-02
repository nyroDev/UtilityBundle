<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use NyroDev\UtilityBundle\Services\NyrodevService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ContainerInterfaceServiceableTrait
{
    protected ?ContainerInterface $container = null;

    #[Required]
    public function setContainerInterface(
        #[Autowire('@service_container')]
        ContainerInterface $container,
    ): void {
        $this->container = $container;
    }

    protected function getContainerInterface(): ?ContainerInterface
    {
        return $this->container;
    }

    public function hasParameter(string $parameter): bool
    {
        return $this->container->hasParameter($parameter);
    }

    public function getParameter(string $parameter, mixed $default = null): mixed
    {
        $value = $this->hasParameter($parameter) ? $this->container->getParameter($parameter) : null;

        return !is_null($value) ? $value : $default;
    }

    public function get(string $id): mixed
    {
        return $this->container->get($id);
    }

    public function trans(string $key, array $parameters = [], string $domain = 'messages', ?string $locale = null): string
    {
        return $this->container->get('translator')->trans($key, $parameters, $domain, $locale);
    }

    public function generateUrl(string $route, array $parameters = [], bool $absolute = false): string
    {
        return $this->container->get(NyrodevService::class)->generateUrl($route, $parameters, $absolute);
    }
}
