<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filter Type for Date range fields.
 */
class FilterRangeDateType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($builder->has('transformer')) {
            $builder->remove('transformer');
        }
        $builder
            ->add('value', FilterRangeSubType::class, array_merge([
                'type' => DateType::class,
                'isDate' => true,
                'required' => false,
                'attr' => [
                    'class' => 'filterFormRange',
                ],
            ], $options['valueOptions']));
    }

    public function getDefaultTransformer(): string
    {
        return AbstractQueryBuilder::OPERATOR_EQUALS;
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

    public function applyValue(mixed $value): array
    {
        if (isset($value['start']) && is_object($value['start'])) {
            $value['start']->setTime(0, 0, 0);
        }
        if (isset($value['end']) && is_object($value['end'])) {
            $value['end']->setTime(23, 59, 59);
        }

        return $value;
    }

    public function getBlockPrefix(): string
    {
        return 'filter_range_date';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
