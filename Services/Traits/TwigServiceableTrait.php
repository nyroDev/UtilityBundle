<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Twig\Environment;

trait TwigServiceableTrait
{
    protected $twig;

    /**
     * @Required
     */
    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;
    }

    protected function getTwig(): Environment
    {
        return $this->twig;
    }
}
