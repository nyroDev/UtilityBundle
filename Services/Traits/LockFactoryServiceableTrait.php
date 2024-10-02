<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Service\Attribute\Required;

trait LockFactoryServiceableTrait
{
    protected ?LockFactory $lockFactory = null;

    #[Required]
    public function setLockFactory(LockFactory $lockFactory): void
    {
        $this->lockFactory = $lockFactory;
    }

    protected function getLockFactory(): ?LockFactory
    {
        return $this->lockFactory;
    }
}
