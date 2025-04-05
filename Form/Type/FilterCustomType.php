<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Default Filter Type field for text fields.
 */
class FilterCustomType extends FilterType
{
    protected $applyFilters = [];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->applyFilters[$builder->getName()] = $options['applyFilter'];
        if ($options['showTransformer']) {
            if (isset($options['transformerChoices']) && $options['transformerChoices'] && count($options['transformerChoices'])) {
                $builder
                    ->add('transformer', ChoiceType::class, array_merge([
                        'choices' => $options['transformerChoices'],
                    ], $options['transformerOptions']));
            } elseif ($builder->has('transformer')) {
                $builder->remove('transformer');
            }
        }
        $builder
            ->add('value', $options['valueType'], array_merge([
                'required' => false,
            ], $options['valueOptions']));
    }

    public function getDefaultTransformer(): string
    {
        return AbstractQueryBuilder::OPERATOR_EQUALS;
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, string $name, array $data): AbstractQueryBuilder
    {
        $applyFilter = $this->applyFilters[$name];
        if (!isset($data['transformer'])) {
            $data['transformer'] = $this->getDefaultTransformer();
        }

        return $applyFilter ? $applyFilter($queryBuilder, $name, $data) : $queryBuilder;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'applyFilter' => null,
            'transformerChoices' => [
                '=' => AbstractQueryBuilder::OPERATOR_EQUALS,
            ],
            'transformerOptions' => [],
            'valueType' => TextType::class,
            'valueOptions' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'filter_custom';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
