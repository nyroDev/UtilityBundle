<?php

namespace NyroDev\UtilityBundle\Services\Db;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use NyroDev\UtilityBundle\QueryBuilder\ElasticaQueryBuilder;
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

    public function getRepository(object|string $name): ObjectRepository
    {
        return is_object($name) ? $name : $this->getObjectManager()->getRepository($name);
    }

    public function getQueryBuilder(string|ObjectRepository $name, bool $getElasticaQb = false): AbstractQueryBuilder
    {
        if ($getElasticaQb) {
            $class = ElasticaQueryBuilder::class;
        } else {
            $class = $this->getParameter('nyroDev_utility.queryBuilder.class');
        }

        return new $class(is_object($name) ? $name : $this->getRepository($name), $this->getObjectManager(), $this);
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
