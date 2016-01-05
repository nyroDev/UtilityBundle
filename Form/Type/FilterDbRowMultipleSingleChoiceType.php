<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Filter Type for Integer fields 
 */
class FilterDbRowMultipleSingleChoiceType extends FilterDbRowType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$nyrodevDb = $this->get('nyrodev_db');
		$builder
			->add('transformer', ChoiceType::class, array_merge(array(
				'choices'=>array(
					AbstractQueryBuilder::OPERATOR_IN=>'=',
				),
			), $options['transformerOptions']))
			->add('value', $this->get('nyrodev_db')->getFormType(), array_merge(array(
					'required'=>false,
					'multiple'=>false,
					'class'=>$options['class'],
					'property'=>isset($options['property']) ? $options['property'] : null,
					'query_builder' => isset($options['where']) || isset($options['order']) ? function(ObjectRepository $er) use($options, $nyrodevDb) {
						$ret = $nyrodevDb->getQueryBuilder($er);
						/* @var $ret \NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder */
						
						if (isset($options['where']) && is_array($options['where'])) {
							foreach($options['where'] as $k=>$v) {
								if (is_int($k)) {
									if (is_array($v)) {
										$ret->addWhere($v[0], $v[1], isset($v[2]) ? $v[2] : null);
									} else {
										throw new \RuntimeException('Direct where setting is not supported anymore.');
									}
								} else if (is_array($v)) {
									$ret->addWhere($k, AbstractQueryBuilder::OPERATOR_IN, $v);
								} else {
									$ret->addWhere($k, AbstractQueryBuilder::OPERATOR_EQUALS, $v);
								}
							}
						}
						if (isset($options['order']))
							$ret->orderBy($options['order'], 'ASC');
						
						return $ret->getQueryBuilder();
					} : null
				), $options['valueOptions']));
	}
	
    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data) {
		if (
				isset($data['transformer']) && $data['transformer']
			&&  isset($data['value']) && $data['value']
			) {
			$value = $this->applyValue($data['value']);

			if (count($value) > 0)
				$queryBuilder->addJoinWhere($name, array($value));
		}
		
		return $queryBuilder;
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
		return 'filter_dbRowMultipleSingleChoice';
	}
	
	public function getParent() {
		return FilterType::class;
	}

}