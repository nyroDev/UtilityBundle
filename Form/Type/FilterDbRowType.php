<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter Type for Integer fields 
 */
class FilterDbRowType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('transformer', 'choice', array(
				'choices'=>array(
					'='=>'=',
				),
			))
			->add('value', 'entity', array(
					'required'=>false,
					'class'=>$options['class'],
					'property'=>isset($options['property']) ? $options['property'] : null,
					'query_builder' => isset($options['where']) || isset($options['order']) ? function(EntityRepository $er) use($options) {
						$ret = $er->createQueryBuilder('l');
						if (isset($options['where']) && is_array($options['where'])) {
							$nb = 1;
							foreach($options['where'] as $k=>$v) {
								if (is_int($k)) {
									$ret->andWhere('l.'.$v);
								} else if (is_array($v)) {
									$ret->andWhere($ret->expr()->in('l.'.$k, $v));
									$nb++;
								} else {
									$ret
										->andWhere('l.'.$k.' = :param'.$nb)
										->setParameter('param'.$nb, $v);
									$nb++;
								}
							}
						}
						if (isset($options['order']))
							$ret->orderBy('l.'.$options['order'], 'ASC');
						return $ret;
					} : null
				));
	}
	
	public function applyValue($value) {
		return $value->getId();
	}
	
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'class'=>null,
			'property'=>null,
			'where'=>null,
			'order'=>null,
		));
    }
	
	public function getName() {
		return 'filter_dbRow';
	}
	
	public function getParent() {
		return 'filter';
	}

}