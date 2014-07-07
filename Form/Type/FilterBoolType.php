<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filter Type for Boolean fields 
 */
class FilterBoolType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('transformer', 'choice', array(
				'choices'=>array(
					'='=>'='
				),
			))
			->add('value', 'choice', array(
					'required'=>false,
					'choices'=>array(
						'1'=>'Oui',
						'no'=>'Non'
					),
				));
	}
	
	public function applyValue($value) {
		return $value == 'no' ? 0 : 1;
	}
	
	public function getName() {
		return 'filter_bool';
	}
	
	public function getParent() {
		return 'filter';
	}

}