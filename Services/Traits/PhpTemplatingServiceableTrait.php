<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use NyroDev\PhpTemplateBundle\Templating\PhpEngine;
use Symfony\Contracts\Service\Attribute\Required;

trait PhpTemplatingServiceableTrait
{
    protected ?PhpEngine $phpTemplating = null;

    #[Required]
    public function setPhpTemplating(PhpEngine $phpTemplating): void
    {
        $this->phpTemplating = $phpTemplating;
    }

    protected function getPhpTemplating(): ?PhpEngine
    {
        return $this->phpTemplating;
    }
}
