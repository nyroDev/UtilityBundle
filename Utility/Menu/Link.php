<?php

namespace NyroDev\UtilityBundle\Utility\Menu;

class Link extends Menuable implements MenuActivableInterface
{
    public function __construct(
        public string $url,
        public string $label,
        public bool $active = false,
        public string $icon = '',
        public array $attrs = [],
        public bool $goBlank = false,
    ) {
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
