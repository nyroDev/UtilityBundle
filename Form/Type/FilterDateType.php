<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filter Type for Date fields
 */
class FilterDateType extends FilterType {
	
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
			->add('value', 'date', array(
					'required'=>false
				));
	}
	
	public function applyValue($value) {
		return is_object($value) ? $value->format('Y-m-d') : $value;
	}
	
	public function getName() {
		return 'filter_date';
	}
	
	public function getParent() {
		return 'filter';
	}

}