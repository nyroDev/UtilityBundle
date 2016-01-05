<?php

namespace NyroDev\UtilityBundle\Services\Db;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;

class MongodbService extends AbstractService {

	public function getFormType() {
		return DocumentType::class;
	}

}