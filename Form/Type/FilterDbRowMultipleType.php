<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter Type for Integer fields 
 */
class FilterDbRowMultipleType extends FilterDbRowType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('transformer', 'choice', array(
				'choices'=>array(
					'IN'=>'IN',
				),
			))
			->add('value', 'entity', array(
					'required'=>false,
					'multiple'=>true,
					'attr'=>array(
						'class'=>'multiple'
					),
					'class'=>$options['class'],
					'property'=>isset($options['property']) ? $options['property'] : null,
					'query_builder' => isset($options['where']) || isset($options['order']) ? function(EntityRepository $er) use($options) {
						$ret = $er->createQueryBuilder('l');
						if (isset($options['where']) && is_array($options['where'])) {
							$nb = 1;
							foreach($options['where'] as $k=>$v) {
								if (is_int($k)) {
									$ret->andWhere('l.'.$v);
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
	
    public function applyFilter(QueryBuilder $queryBuilder, $name, $data) {
		if (
				isset($data['transformer']) && $data['transformer']
			&&  isset($data['value']) && $data['value']
			) {
			$value = $this->applyValue($data['value']);

			if (count($value) > 0) {
				$paramName = $name.'_param';
				$queryBuilder
						->join($queryBuilder->getRootAlias().'.'.$name, $name)
						->andWhere($name.'.id IN (:'.$paramName.')')
						->setParameter($paramName, $value);
			}
		}
		
		return $queryBuilder;
    }
	
	public function applyValue($value) {
		$ret = array();
		foreach($value as $val)
			$ret[] = $val->getId();
		return array_filter($ret);
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
		return 'filter_dbRowMultiple';
	}
	
	public function getParent() {
		return 'filter';
	}

}