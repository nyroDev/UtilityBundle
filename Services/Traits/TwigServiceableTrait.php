<?php

namespace NyroDev\UtilityBundle\Services\Traits;

use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

trait TwigServiceableTrait
{
    protected ?Environment $twig = null;

    #[Required]
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    protected function getTwig(): Environment
    {
        return $this->twig;
    }
}
