<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Doctrine\DBAL\ParameterType;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Default Filter Type field for text fields.
 */
class FilterType extends AbstractType implements FilterTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [
            'LIKE %...%' => AbstractQueryBuilder::OPERATOR_CONTAINS,
            '=' => AbstractQueryBuilder::OPERATOR_EQUALS,
        ];
        if ($options['addNullTransformer']) {
            $choices['IS NULL'] = AbstractQueryBuilder::OPERATOR_IS_NULL;
            $choices['IS NOT NULL'] = AbstractQueryBuilder::OPERATOR_IS_NOT_NULL;
        }
        $builder
            ->add('transformer', ChoiceType::class, array_merge([
                'choices' => $choices,
            ], $options['transformerOptions']))
            ->add('value', TextType::class, array_merge([
                'required' => false,
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

            if (AbstractQueryBuilder::OPERATOR_IS_NULL == $transformer || AbstractQueryBuilder::OPERATOR_IS_NOT_NULL == $transformer) {
                $queryBuilder->addWhere($name, $transformer);
            } else {
                $queryBuilder->addWhere($name, $transformer, $value, ParameterType::STRING);
            }
        }

        return $queryBuilder;
    }

    public function applyValue(mixed $value): mixed
    {
        return $value;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'addNullTransformer' => false,
            'transformerOptions' => [],
            'valueOptions' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }

    public function getParent(): ?string
    {
        return FormType::class;
    }
}
