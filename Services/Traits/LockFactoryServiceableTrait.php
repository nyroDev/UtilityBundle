<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Component\Lock\LockFactory;

trait LockFactoryServiceableTrait
{
    protected $lockFactory;

    /**
     * @Required
     */
    public function setLockFactory(LockFactory $lockFactory)
    {
        $this->lockFactory = $lockFactory;
    }

    protected function getLockFactory(): LockFactory
    {
        return $this->lockFactory;
    }
}
