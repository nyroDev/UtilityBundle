<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter Type for Integer fields 
 */
class FilterDbRowMultipleType extends FilterDbRowType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$nyrodevDb = $this->get('nyrodev_db');
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
					'query_builder' => isset($options['where']) || isset($options['order']) ? function(ObjectRepository $er) use($options, $nyrodevDb) {
						$ret = $nyrodevDb->getQueryBuilder($er);
						/* @var $ret \NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder */
						
						if (isset($options['where']) && is_array($options['where'])) {
							foreach($options['where'] as $k=>$v) {
								if (is_int($k)) {
									throw new \RuntimeException('Direct where setting is not supported anymore.');
								} else if (is_array($v)) {
									$ret->addWhere($k, 'in', $v);
								} else {
									$ret->addWhere($k, '=', $v);
								}
							}
						}
						if (isset($options['order']))
							$ret->orderBy($options['order'], 'ASC');
						
						return $ret->getQueryBuilder();
					} : null
				));
	}
	
    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data) {
		if (
				isset($data['transformer']) && $data['transformer']
			&&  isset($data['value']) && $data['value']
			) {
			$value = $this->applyValue($data['value']);

			if (count($value) > 0)
				$queryBuilder->addJoinWhere($name, $value);
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