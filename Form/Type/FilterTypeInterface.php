<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface to be used for FilterType elements
 */
interface FilterTypeInterface {

	/**
	 * Apply the filter to the given QueryBuilder
	 *
	 * @param QueryBuilder $queryBuilder
	 * @param string $name Field name
	 * @param array $data Data for the field
	 * @return QueryBuilder
	 */
	public function applyFilter(QueryBuilder $queryBuilder, $name, $data);
	
	/**
	 * Prepare a value to be applied into QueryBuilder.
	 * Useful for Object values 
	 */
	public function applyValue($value);

}