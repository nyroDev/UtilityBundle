<?php

namespace NyroDev\UtilityBundle\Listener;

use Gedmo\Loggable\LoggableListener as SrcLoggableListener;

class LoggableListener extends SrcLoggableListener
{
    protected function prePersistLogEntry($logEntry, $object)
    {
        if (method_exists($object, 'getTranslatableLocale') && method_exists($logEntry, 'setLocale') && $object->getTranslatableLocale()) {
            $locale = $object->getTranslatableLocale();
            if (strpos($locale, 'change_') === 0) {
                $tmp = explode('change_', $locale);
                $locale = $tmp[1];
            }
            $logEntry->setLocale($locale);
        }
    }
}
