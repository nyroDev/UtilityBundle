<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter Type for Integer fields 
 */
class FilterDbRowType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$nyrodevDb = $this->get('nyrodev_db');
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
					'query_builder' => isset($options['where']) || isset($options['order']) ? function(ObjectRepository $er) use($options, $nyrodevDb) {
						$ret = $nyrodevDb->getQueryBuilder($er);
						/* @var $ret \NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder */
						
						if (isset($options['where']) && is_array($options['where'])) {
							foreach($options['where'] as $k=>$v) {
								if (is_int($k)) {
									if (is_array($v)) {
										$ret->addWhere($v[0], $v[1], $v[2]);
									} else {
										throw new \RuntimeException('Direct where setting is not supported anymore.');
									}
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