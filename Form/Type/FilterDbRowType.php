<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Filter Type for Integer fields 
 */
class FilterDbRowType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$nyrodevDb = $this->get('nyrodev_db');
		$builder
			->add('transformer', ChoiceType::class, array(
				'choices'=>array(
					'='=>'=',
				),
			))
			->add('value', EntityType::class, array(
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
    public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'class'=>null,
			'property'=>null,
			'where'=>null,
			'order'=>null,
		));
    }
	
	public function getBlockPrefix() {
		return 'filter_dbRow';
	}
	
	public function getParent() {
		return FilterType::class;
	}

}