<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Component\Asset\Packages;

trait AssetsPackagesServiceableTrait
{
    protected $assetsPackages;

    /**
     * @Required
     */
    public function setAssetsPackages(Packages $assetsPackages)
    {
        $this->assetsPackages = $assetsPackages;
    }

    protected function getAssetsPackages(): Packages
    {
        return $this->assetsPackages;
    }
}
