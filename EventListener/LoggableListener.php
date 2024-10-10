<?php

namespace NyroDev\UtilityBundle\EventListener;

use Gedmo\Loggable\LoggableListener as SrcLoggableListener;

class LoggableListener extends SrcLoggableListener
{
    protected function prePersistLogEntry($logEntry, $object): void
    {
        if (method_exists($object, 'getTranslatableLocale') && method_exists($logEntry, 'setLocale') && $object->getTranslatableLocale()) {
            $locale = $object->getTranslatableLocale();
            if (0 === strpos($locale, 'change_')) {
                $tmp = explode('change_', $locale);
                $locale = $tmp[1];
            }
            $logEntry->setLocale($locale);
        }
    }
}
