<?php

namespace NyroDev\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SrcAbstractController;

abstract class AbstractController extends SrcAbstractController
{
    use Traits\SubscribedServiceTrait;

    /**
     * Get the translation for a given keyword.
     *
     * @param string $key        Translation key
     * @param array  $parameters Parameters to replace
     * @param string $domain     Translation domain
     * @param string $locale     Local to use
     *
     * @return string The translation
     */
    protected function trans($key, array $parameters = [], $domain = 'messages', $locale = null)
    {
        return $this->get('translator')->trans($key, $parameters, $domain, $locale);
    }
}
