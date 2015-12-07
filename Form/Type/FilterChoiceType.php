<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Filter Type for Integer fields 
 */
class FilterChoiceType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('transformer', ChoiceType::class, array(
				'choices'=>array(
					'='=>'=',
				),
			))
			->add('value', ChoiceType::class, array_merge($options['choiceOptions'], array(
					'required'=>false,
				)));
	}
	
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
		$resolver->setRequired(array('choiceOptions'));
    }
	
	public function getBlockPrefix() {
		return 'filter_choice';
	}
	
	public function getParent() {
		return FilterType::class;
	}

}