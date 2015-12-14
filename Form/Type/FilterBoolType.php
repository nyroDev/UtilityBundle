<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Filter Type for Boolean fields 
 */
class FilterBoolType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('transformer', ChoiceType::class, array_merge(array(
				'choices'=>array(
					'='=>'='
				),
			), $options['transformerOptions']))
			->add('value', ChoiceType::class, array_merge(array(
					'required'=>false,
					'choices'=>array(
						'1'=>'Oui',
						'no'=>'Non'
					),
				), $options['valueOptions']));
	}
	
	public function applyValue($value) {
		return $value == 'no' ? 0 : 1;
	}
	
	public function getBlockPrefix() {
		return 'filter_bool';
	}
	
	public function getParent() {
		return FilterType::class;
	}

}