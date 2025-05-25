<?php

namespace NyroDev\UtilityBundle\Helper;

use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use NyroDev\UtilityBundle\Services\Traits\KernelInterfaceServiceableTrait;

class IconHelper extends AbstractService
{
    use KernelInterfaceServiceableTrait;
    use AssetsPackagesServiceableTrait;

    public static function getAlias()
    {
        return 'icon';
    }

    private function getSvgUrl(string $svgFile): string
    {
        $realFilePath = $this->getKernelInterface()->getProjectDir().'/public/'.$svgFile;

        return $this->getAssetsPackages()->getUrl($svgFile).'?t='.filemtime($realFilePath);
    }

    private function getSvgTag(string $url, string $name, ?string $class = null, ?string $attrs = null): string
    {
        if ($class) {
            $class = ' '.$class;
        }

        return '<svg class="icon icon-'.$name.$class.'" '.$attrs.'><use href="'.$url.'#'.$name.'"></use></svg>';
    }

    public function getIcon(string $name, ?string $class = null, ?string $attrs = null, $svgFile = 'images/icons.svg'): string
    {
        if (false !== strpos($name, '#')) {
            $tmp = explode('#', $name);
            $svgFile = $tmp[0];
            $name = $tmp[1];
        }

        return $this->getSvgTag($this->getSvgUrl($svgFile), $name, $class, $attrs);
    }
}
