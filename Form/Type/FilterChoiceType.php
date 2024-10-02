<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter Type for Integer fields.
 */
class FilterChoiceType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transformer', ChoiceType::class, array_merge([
                'choices' => [
                    AbstractQueryBuilder::OPERATOR_EQUALS => '=',
                ],
            ], $options['transformerOptions']))
            ->add('value', ChoiceType::class, array_merge($options['choiceOptions'], [
                'required' => false,
            ], $options['valueOptions']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['choiceOptions']);
    }

    public function getBlockPrefix()
    {
        return 'filter_choice';
    }

    public function getParent()
    {
        return FilterType::class;
    }
}
