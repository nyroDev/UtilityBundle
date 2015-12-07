<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

/**
 * Filter Type for Date fields
 */
class FilterDateType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('transformer', ChoiceType::class, array(
				'choices'=>array(
					'='=>'=',
					'>='=>'>=',
					'<='=>'<=',
					'>'=>'>',
					'<'=>'<',
				),
			))
			->add('value', DateType::class, array(
					'required'=>false
				));
	}
	
	public function applyValue($value) {
		return is_object($value) ? $value->format('Y-m-d') : $value;
	}
	
	public function getBlockPrefix() {
		return 'filter_date';
	}
	
	public function getParent() {
		return FilterType::class;
	}

}