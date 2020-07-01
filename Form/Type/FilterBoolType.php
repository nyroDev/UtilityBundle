<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filter Type for Boolean fields.
 */
class FilterBoolType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transformer', ChoiceType::class, array_merge([
                'choices' => [
                    AbstractQueryBuilder::OPERATOR_EQUALS => '=',
                ],
            ], $options['transformerOptions']))
            ->add('value', ChoiceType::class, array_merge([
                    'required' => false,
                    'choices' => [
                        'Yes' => '1',
                        'No' => 'no',
                    ],
                ], $options['valueOptions']));
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data)
    {
        if (
                isset($data['transformer']) && $data['transformer']
            && isset($data['value']) && $data['value']
            ) {
            $value = $this->applyValue($data['value']);
            $transformer = $data['transformer'];

            if (false === $value) {
                $queryBuilder->addWhere(AbstractQueryBuilder::WHERE_OR, [
                    [$name, AbstractQueryBuilder::OPERATOR_EQUALS, false],
                    [$name, AbstractQueryBuilder::OPERATOR_IS_NULL],
                ]);
            } else {
                $queryBuilder->addWhere($name, $transformer, $value);
            }
        }

        return $queryBuilder;
    }

    public function applyValue($value)
    {
        return 'no' == $value ? false : true;
    }

    public function getBlockPrefix()
    {
        return 'filter_bool';
    }

    public function getParent()
    {
        return FilterType::class;
    }
}
