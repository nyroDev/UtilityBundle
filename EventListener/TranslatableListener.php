<?php

namespace NyroDev\UtilityBundle\EventListener;

use Doctrine\Common\EventArgs;
use Gedmo\Translatable\TranslatableListener as SrcTranslatableListener;

class TranslatableListener extends SrcTranslatableListener
{
    public function postLoad(EventArgs $args)
    {
        parent::postLoad($args);

        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $object = $ea->getObject();
        $meta = $om->getClassMetadata(get_class($object));
        $config = $this->getConfiguration($om, $meta->name);

        if (isset($config['fields']) && method_exists($object, 'setTranslatableLocale')) {
            $object->setTranslatableLocale($this->getTranslatableLocale($object, $meta, $om));
        }
    }
}
