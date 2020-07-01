<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Component\HttpKernel\KernelInterface;

trait KernelInterfaceServiceableTrait
{
    protected $kernelInterface;

    /**
     * @Required
     */
    public function setKernelInterface(KernelInterface $kernelInterface)
    {
        $this->kernelInterface = $kernelInterface;
    }

    protected function getKernelInterface(): KernelInterface
    {
        return $this->kernelInterface;
    }
}
