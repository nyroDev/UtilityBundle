<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Service\Attribute\Required;

trait AssetsPackagesServiceableTrait
{
    protected ?Packages $assetsPackages = null;

    #[Required]
    public function setAssetsPackages(Packages $assetsPackages): void
    {
        $this->assetsPackages = $assetsPackages;
    }

    protected function getAssetsPackages(): ?Packages
    {
        return $this->assetsPackages;
    }
}
