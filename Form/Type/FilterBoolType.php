<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;

/**
 * Filter Type for Boolean fields.
 */
class FilterBoolType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transformer', ChoiceType::class, array_merge(array(
                'choices' => array(
                    AbstractQueryBuilder::OPERATOR_EQUALS => '=',
                ),
            ), $options['transformerOptions']))
            ->add('value', ChoiceType::class, array_merge(array(
                    'required' => false,
                    'choices' => array(
                        'Yes' => '1',
                        'No' => 'no',
                    ),
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

            if ($value === false) {
                $queryBuilder->addWhere(AbstractQueryBuilder::WHERE_OR, array(
                    array($name, AbstractQueryBuilder::OPERATOR_EQUALS, false),
                    array($name, AbstractQueryBuilder::OPERATOR_IS_NULL),
                ));
            } else {
                $queryBuilder->addWhere($name, $transformer, $value);
            }
        }

        return $queryBuilder;
    }

    public function applyValue($value)
    {
        return $value == 'no' ? false : true;
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
