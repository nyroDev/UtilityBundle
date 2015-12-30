<?php

namespace NyroDev\UtilityBundle\Services\Db;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class OrmService extends AbstractService {

	public function getFormType() {
		return EntityType::class;
	}

}