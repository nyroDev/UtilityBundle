<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait KernelInterfaceServiceableTrait
{
    protected ?KernelInterface $kernelInterface = null;

    #[Required]
    public function setKernelInterface(KernelInterface $kernelInterface): void
    {
        $this->kernelInterface = $kernelInterface;
    }

    protected function getKernelInterface(): ?KernelInterface
    {
        return $this->kernelInterface;
    }
}
