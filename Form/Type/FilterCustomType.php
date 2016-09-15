<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Default Filter Type field for text fields.
 */
class FilterCustomType extends FilterType
{
    
    protected $applyFilters = [];
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->applyFilters[$builder->getName()] = $options['applyFilter'];
        if (isset($options['transformerChoices']) && $options['transformerChoices'] && count($options['transformerChoices'])) {
            $builder
                ->add('transformer', ChoiceType::class, array_merge(array(
                    'choices' => $options['transformerChoices'],
                ), $options['transformerOptions']));
        } else if ($builder->has('transformer')) {
            $builder->remove('transformer');
        }
        $builder
            ->add('value', $options['valueType'], array_merge(array(
                'required' => false,
            ), $options['valueOptions']));
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data)
    {
        $applyFilter = $this->applyFilters[$name];
        return $applyFilter ? $applyFilter($queryBuilder, $name, $data) : $queryBuilder;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'applyFilter' => null,
            'transformerChoices' => array(
                AbstractQueryBuilder::OPERATOR_EQUALS => '=',
            ),
            'transformerOptions' => array(),
            'valueType' => TextType::class,
            'valueOptions' => array(),
        ));
    }

    public function getBlockPrefix()
    {
        return 'filter_custom';
    }

    public function getParent()
    {
        return FilterType::class;
    }
}
