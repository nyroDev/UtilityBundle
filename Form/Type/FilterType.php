<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use PDO;
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
    /**
     * Builds the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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

    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data)
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
                $queryBuilder->addWhere($name, $transformer, $value, PDO::PARAM_STR);
            }
        }

        return $queryBuilder;
    }

    public function applyValue($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'addNullTransformer' => false,
            'transformerOptions' => [],
            'valueOptions' => [],
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'filter';
    }

    /**
     * Returns the name of the parent type.
     *
     * @return string|null The name of the parent type if any otherwise null
     */
    public function getParent()
    {
        return FormType::class;
    }
}
