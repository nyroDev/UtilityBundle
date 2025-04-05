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
     */
    public function applyFilter(AbstractQueryBuilder $queryBuilder, string $name, array $data): AbstractQueryBuilder;

    /**
     * Define the transforme to use when showTransformer is set to false.
     */
    public function getDefaultTransformer(): string;

    /**
     * Prepare a value to be applied into AbstractQueryBuilder.
     * Useful for Object values.
     */
    public function applyValue(mixed $value): mixed;
}
