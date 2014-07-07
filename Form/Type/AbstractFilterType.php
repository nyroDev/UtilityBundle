<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractFilterType extends AbstractType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->setMethod('get')
			->add('submit', 'submit', array('label'=>$this->trans('admin.misc.filter')));
	}

}