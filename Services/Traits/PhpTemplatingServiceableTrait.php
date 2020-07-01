<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use NyroDev\PhpTemplateBundle\Templating\PhpEngine;

trait PhpTemplatingServiceableTrait
{
    protected $phpTemplating;

    /**
     * @Required
     */
    public function setPhpTemplating(PhpEngine $phpTemplating)
    {
        $this->phpTemplating = $phpTemplating;
    }

    protected function getPhpTemplating(): PhpEngine
    {
        return $this->phpTemplating;
    }
}
