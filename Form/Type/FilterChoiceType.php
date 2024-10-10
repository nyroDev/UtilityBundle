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
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['choiceOptions']);
    }

    public function getBlockPrefix(): string
    {
        return 'filter_choice';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
