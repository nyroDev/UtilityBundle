<?php

namespace NyroDev\UtilityBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TinymceEvent extends Event
{
    public const BROWSER_CONFIG = 'nyrodev.events.tinymce.browser.config';

    protected ?array $config = null;

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(?array $config): void
    {
        $this->config = $config;
    }
}
