<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

/**
 * Filter Type for Date fields.
 */
class FilterDateType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transformer', ChoiceType::class, array_merge(array(
                'choices' => array(
                    '=' => AbstractQueryBuilder::OPERATOR_LIKEDATE,
                    '>=' => AbstractQueryBuilder::OPERATOR_GTE,
                    '<=' => AbstractQueryBuilder::OPERATOR_LTE,
                    '>' => AbstractQueryBuilder::OPERATOR_GT,
                    '<' => AbstractQueryBuilder::OPERATOR_LT,
                ),
            ), $options['transformerOptions']))
            ->add('value', DateType::class, array_merge(array(
                    'required' => false,
                ), $options['valueOptions']));
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data)
    {
        if (
                isset($data['transformer']) && $data['transformer']
            &&  isset($data['value']) && $data['value']
            ) {
            $value = $this->applyValue($data['value']);
            $transformer = $data['transformer'];

            $queryBuilder->addWhere($name, $transformer, $value, \PDO::PARAM_STR);
        }

        return $queryBuilder;
    }

    public function applyValue($value)
    {
        return is_object($value) ? $value->format('Y-m-d') : $value;
    }

    public function getBlockPrefix()
    {
        return 'filter_date';
    }

    public function getParent()
    {
        return FilterType::class;
    }
}
