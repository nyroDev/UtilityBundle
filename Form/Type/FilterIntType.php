<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filter Type for Integer fields 
 */
class FilterIntType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('transformer', 'choice', array(
				'choices'=>array(
					'='=>'=',
					'>='=>'>=',
					'<='=>'<=',
					'>'=>'>',
					'<'=>'<',
				),
			))
			->add('value', 'integer', array(
					'required'=>false,
				));
	}
	
	public function getName() {
		return 'filter_int';
	}
	
	public function getParent() {
		return 'filter';
	}

}