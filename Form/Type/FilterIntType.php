<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * Filter Type for Integer fields 
 */
class FilterIntType extends FilterType {
	
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
			->add('value', IntegerType::class, array(
					'required'=>false,
				));
	}
	
	public function getBlockPrefix() {
		return 'filter_int';
	}
	
	public function getParent() {
		return FilterType::class;
	}

}