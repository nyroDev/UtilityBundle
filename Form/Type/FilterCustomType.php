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
    
    protected $applyFilter;
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->applyFilter = $options['applyFilter'];
        $builder
            ->add('transformer', ChoiceType::class, array_merge(array(
                'choices' => $options['transformerChoices'],
            ), $options['transformerOptions']))
            ->add('value', $options['valueType'], array_merge(array(
                'required' => false,
            ), $options['valueOptions']));
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data)
    {
        $applyFilter = $this->applyFilter;
        return $applyFilter($queryBuilder, $name, $data);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('applyFilter');
        $resolver->setDefaults(array(
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
