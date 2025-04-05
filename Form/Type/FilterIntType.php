<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filter Type for Integer fields.
 */
class FilterIntType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['showTransformer']) {
            $builder
                ->add('transformer', ChoiceType::class, array_merge([
                    'choices' => [
                        '=' => AbstractQueryBuilder::OPERATOR_EQUALS,
                        '>=' => AbstractQueryBuilder::OPERATOR_GTE,
                        '<=' => AbstractQueryBuilder::OPERATOR_LTE,
                        '>' => AbstractQueryBuilder::OPERATOR_GT,
                        '<' => AbstractQueryBuilder::OPERATOR_LT,
                    ],
                ], $options['transformerOptions']))
            ;
        }
        $builder
            ->add('value', IntegerType::class, array_merge([
                'required' => false,
            ], $options['valueOptions']));
    }

    public function getDefaultTransformer(): string
    {
        return AbstractQueryBuilder::OPERATOR_EQUALS;
    }

    public function getBlockPrefix(): string
    {
        return 'filter_int';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
