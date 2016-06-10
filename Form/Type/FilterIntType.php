<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;

/**
 * Filter Type for Integer fields.
 */
class FilterIntType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transformer', ChoiceType::class, array_merge(array(
                'choices' => array(
                    AbstractQueryBuilder::OPERATOR_EQUALS => '=',
                    AbstractQueryBuilder::OPERATOR_GTE => '>=',
                    AbstractQueryBuilder::OPERATOR_LTE => '<=',
                    AbstractQueryBuilder::OPERATOR_GT => '>',
                    AbstractQueryBuilder::OPERATOR_LT => '<',
                ),
            ), $options['transformerOptions']))
            ->add('value', IntegerType::class, array_merge(array(
                    'required' => false,
                ), $options['valueOptions']));
    }

    public function getBlockPrefix()
    {
        return 'filter_int';
    }

    public function getParent()
    {
        return FilterType::class;
    }
}
