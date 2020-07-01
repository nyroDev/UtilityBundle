<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;

/**
 * Interface to be used for FilterType elements.
 */
interface FilterTypeInterface
{
    /**
     * Apply the filter to the given QueryBuilder.
     *
     * @param string $name Field name
     * @param array  $data Data for the field
     *
     * @return AbstractQueryBuilder
     */
    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data);

    /**
     * Prepare a value to be applied into AbstractQueryBuilder.
     * Useful for Object values.
     */
    public function applyValue($value);
}
