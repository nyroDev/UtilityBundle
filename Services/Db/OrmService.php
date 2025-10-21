<?php

namespace NyroDev\UtilityBundle\Services\Db;

use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class OrmService extends DbAbstractService
{
    public function getFormType(): string
    {
        return EntityType::class;
    }

    public function getClassMetadata(object|string $name): ClassMetadata
    {
        $repository = $this->getRepository($name);

        return $this->getObjectManager()->getClassMetadata($repository->getClassName());
    }
}
