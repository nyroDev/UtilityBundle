<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filter Type for Date range fields.
 */
class FilterRangeNumberType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($builder->has('transformer')) {
            $builder->remove('transformer');
        }
        $builder
            ->add('value', FilterRangeSubType::class, array_merge([
                'type' => NumberType::class,
                'required' => false,
                'attr' => [
                    'class' => 'filterFormRange',
                ],
            ], $options['valueOptions']));
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, string $name, array $data): AbstractQueryBuilder
    {
        if (isset($data['value']) && $data['value']) {
            $value = array_filter($this->applyValue($data['value']));

            foreach ($value as $k => $val) {
                $queryBuilder->addWhere($name, 'start' == $k ? AbstractQueryBuilder::OPERATOR_GTE : AbstractQueryBuilder::OPERATOR_LTE, $val);
            }
        }

        return $queryBuilder;
    }

    public function getBlockPrefix(): string
    {
        return 'filter_range_number';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
