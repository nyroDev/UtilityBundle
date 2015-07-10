<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter Type for Integer fields 
 */
class FilterChoiceType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('transformer', 'choice', array(
				'choices'=>array(
					'='=>'=',
				),
			))
			->add('value', 'choice', array_merge($options['choiceOptions'], array(
					'required'=>false,
				)));
	}
	
	public function applyValue($value) {
		return $value->getId();
	}
	
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setRequired(array('choiceOptions'));
    }
	
	public function getName() {
		return 'filter_choice';
	}
	
	public function getParent() {
		return 'filter';
	}

}