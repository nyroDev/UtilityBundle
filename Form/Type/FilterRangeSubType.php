<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType as SrcAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter Type for Date rang sub fields
 */
class FilterRangeSubType extends SrcAbstractType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('start', $options['type'], array_merge(array(
				'label'=>'admin.misc.start',
				'required'=>false,
			), $options['options']))
			->add('end', $options['type'], array_merge(array(
				'label'=>'admin.misc.end',
				'required'=>false,
			), $options['options']));
	}
	
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver
			->setRequired(array('type'))
			->setDefaults(array('options'=>array()));
    }
	
	public function getName() {
		return 'filter_range_sub';
	}
	
	public function getParent() {
		return 'form';
	}

}