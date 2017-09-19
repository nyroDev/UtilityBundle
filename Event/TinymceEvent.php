<?php

namespace NyroDev\UtilityBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class TinymceEvent extends Event
{
    const BROWSER_CONFIG = 'nyrodev.events.tinymce.browser.config';

    protected $config;

    public function getConfig()
    {
        return $this->config;
    }
    
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}
