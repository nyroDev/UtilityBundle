<?php

namespace NyroDev\UtilityBundle\Loader;

use NyroDev\UtilityBundle\Services\AbstractService;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DbLoader extends AbstractService implements LoaderInterface
{
    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = new MessageCatalogue($locale);

        $repo = $this->get('nyrodev_db')->getRepository($this->getParameter('nyroDev_utility.translationDb'));
        foreach ($repo->findBy(array('locale' => $locale, 'domain' => $domain)) as $row) {
            $catalogue->set($row->getIdent(), $row->getTranslation(), $domain);
        }

        return $catalogue;
    }
}
