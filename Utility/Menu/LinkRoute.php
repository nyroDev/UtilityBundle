<?php

namespace NyroDev\UtilityBundle\Utility\Menu;

class LinkRoute extends Menuable implements MenuActivableInterface
{
    public function __construct(
        public string $route,
        public array $routePrm = [],
        public string $label,
        public bool $active = false,
        public string $icon = '',
        public array $attrs = [],
        public bool $goBlank = false,
    ) {
    }

    public function getRoutePrm(): array
    {
        $prmReplace = $this->getVeryParent()->getLinkRoutePrmReplace();

        if (0 === count($prmReplace)) {
            return $this->routePrm;
        }

        return array_map(function ($v) use ($prmReplace) {
            return $prmReplace[$v] ?? $v;
        }, $this->routePrm);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getTemplate(): string
    {
        return '@NyroDevUtility/Menu/link.html.php';
    }
}
