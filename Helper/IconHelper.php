<?php

namespace NyroDev\UtilityBundle\Helper;

use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use NyroDev\UtilityBundle\Services\Traits\KernelInterfaceServiceableTrait;

class IconHelper extends AbstractService
{
    use KernelInterfaceServiceableTrait;
    use AssetsPackagesServiceableTrait;

    public const ICON_PATH = 'bundles/nyrodevutility/images/nyrodevUtility.svg';

    public static function getAlias()
    {
        return 'icon';
    }

    private function getSvgUrl(string $svgFile): string
    {
        return $this->getAssetsPackages()->getUrl($svgFile);
    }

    private function getSvgTag(string $url, string $name, ?string $class = null, ?string $attrs = null): string
    {
        if ($class) {
            $class = ' '.$class;
        }

        return '<svg class="icon icon-'.$name.$class.'" '.$attrs.'><use href="'.$url.'#'.$name.'"></use></svg>';
    }

    public function getIcon(string $name, ?string $class = null, ?string $attrs = null, string $svgFile = self::ICON_PATH): string
    {
        if (false !== strpos($name, '#')) {
            $tmp = explode('#', $name);
            $svgFile = $tmp[0];
            $name = $tmp[1];
        }

        return $this->getSvgTag($this->getSvgUrl($svgFile), $name, $class, $attrs);
    }
}
