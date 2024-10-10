<?php

namespace NyroDev\UtilityBundle\Loader;

use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DbLoader extends AbstractService implements LoaderInterface
{
    public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        $catalogue = new MessageCatalogue($locale);

        $repo = $this->get(DbAbstractService::class)->getRepository($this->getParameter('nyroDev_utility.translationDb'));
        foreach ($repo->findBy(['locale' => $locale, 'domain' => $domain]) as $row) {
            $catalogue->set($row->getIdent(), $row->getTranslation(), $domain);
        }

        return $catalogue;
    }
}
