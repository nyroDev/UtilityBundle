<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Doctrine\DBAL\ParameterType;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Filter Type for Date fields.
 */
class FilterDateType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['showTransformer']) {
            $builder
            ->add('transformer', ChoiceType::class, array_merge([
                'choices' => [
                    '=' => AbstractQueryBuilder::OPERATOR_LIKEDATE,
                    '>=' => AbstractQueryBuilder::OPERATOR_GTE,
                    '<=' => AbstractQueryBuilder::OPERATOR_LTE,
                    '>' => AbstractQueryBuilder::OPERATOR_GT,
                    '<' => AbstractQueryBuilder::OPERATOR_LT,
                ],
            ], $options['transformerOptions']));
        }
        $builder
            ->add('value', DateType::class, array_merge([
                'required' => false,
            ], $options['valueOptions']));
    }

    public function getDefaultTransformer(): string
    {
        return AbstractQueryBuilder::OPERATOR_LIKEDATE;
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, string $name, array $data): AbstractQueryBuilder
    {
        if (isset($data['value']) && $data['value']) {
            $value = $this->applyValue($data['value']);
            $transformer = isset($data['transformer']) && $data['transformer'] ? $data['transformer'] : $this->getDefaultTransformer();

            $queryBuilder->addWhere($name, $transformer, $value, ParameterType::STRING);
        }

        return $queryBuilder;
    }

    public function applyValue(mixed $value): string
    {
        return is_object($value) ? $value->format('Y-m-d') : $value;
    }

    public function getBlockPrefix(): string
    {
        return 'filter_date';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
