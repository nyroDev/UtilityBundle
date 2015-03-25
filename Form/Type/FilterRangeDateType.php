<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Filter Type for Date range fields
 */
class FilterRangeDateType extends FilterType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		if ($builder->has('transformer'))
			$builder->remove('transformer');
		$builder
			->add('value', 'filter_range_sub', array(
					'type'=>'date',
					'required'=>false,
				));
	}
	
    public function applyFilter(QueryBuilder $queryBuilder, $name, $data) {
		if (isset($data['value']) && $data['value']) {
			$value = array_filter($this->applyValue($data['value']));
			
			foreach($value as $k=>$val) {
				$paramName = $name.'_param_'.$k;
				$condMask = '%s.%s %s :%s';

				$condition = sprintf($condMask,
					$queryBuilder->getRootAlias(),
					$name,
					$k == 'start' ? '>=' : '<=',
					$paramName
				);

				$queryBuilder->andWhere($condition)->setParameter($paramName, $val, \PDO::PARAM_STR);
			}
		}
		
		return $queryBuilder;
    }
	
	public function applyValue($value) {
		if (isset($value['start']) && is_object($value['start']))
			$value['start'] = $value['start']->format('Y-m-d');
		if (isset($value['end']) && is_object($value['end']))
			$value['end'] = $value['end']->format('Y-m-d');
		return $value;
	}
	
	public function getName() {
		return 'filter_range_date';
	}
	
	public function getParent() {
		return 'filter';
	}

}