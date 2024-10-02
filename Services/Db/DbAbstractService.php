<?php

namespace NyroDev\UtilityBundle\Services\Db;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use NyroDev\UtilityBundle\Services\AbstractService;

abstract class DbAbstractService extends AbstractService
{
    public function __construct(
        protected readonly ObjectManager $objectManager,
    ) {
    }

    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }

    /**
     * @param string $name class name
     */
    public function getRepository(object|string $name): ObjectRepository
    {
        return is_object($name) ? $name : $this->getObjectManager()->getRepository($name);
    }

    /**
     * @param bool $elastica True to get elastica query Builder
     */
    public function getQueryBuilder(string|ObjectRepository $name, bool $elastica = false): AbstractQueryBuilder
    {
        if ($elastica) {
            $class = \NyroDev\UtilityBundle\QueryBuilder\ElasticaQueryBuilder::class;
        } else {
            $class = $this->getParameter('nyroDev_utility.queryBuilder.class');
        }

        $queryBuilder = new $class(is_object($name) ? $name : $this->getRepository($name), $this->getObjectManager(), $this);

        return $queryBuilder;
    }

    public function getNew(object|string $name, bool $persist = true): mixed
    {
        $repo = $this->getRepository($name);
        $classname = $repo->getClassName();
        $new = new $classname();

        if ($persist) {
            $this->persist($new);
        }

        return $new;
    }

    public function persist(mixed $object): void
    {
        $this->getObjectManager()->persist($object);
    }

    public function remove(mixed $object): void
    {
        $this->getObjectManager()->remove($object);
    }

    public function refresh(mixed $object): void
    {
        $this->getObjectManager()->refresh($object);
    }

    public function flush(): void
    {
        $this->getObjectManager()->flush();
    }

    abstract public function getFormType(): string;
}
