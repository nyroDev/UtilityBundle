<?php

namespace NyroDev\UtilityBundle\Services\Db;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class OrmService extends DbAbstractService
{
    public function getFormType()
    {
        return EntityType::class;
    }
}
