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
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
                    $this->trans('admin.misc.yes') => '1',
                    $this->trans('admin.misc.no') => 'no',
                ],
            ], $options['valueOptions']));
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, string $name, array $data): AbstractQueryBuilder
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

    public function applyValue(mixed $value): bool
    {
        return 'no' == $value ? false : true;
    }

    public function getBlockPrefix(): string
    {
        return 'filter_bool';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
