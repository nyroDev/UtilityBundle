<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\AbstractType as SrcAbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter Type for Date rang sub fields.
 */
class FilterRangeSubType extends SrcAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', $options['type'], array_merge([
                'label' => 'admin.misc.'.($options['isDate'] ? 'start' : 'from'),
                'required' => false,
            ], $options['options']))
            ->add('end', $options['type'], array_merge([
                'label' => 'admin.misc.'.($options['isDate'] ? 'end' : 'to'),
                'required' => false,
            ], $options['options']));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['type'])
            ->setDefaults([
                'isDate' => false,
                'options' => [],
            ]);
    }

    public function getBlockPrefix()
    {
        return 'filter_range_sub';
    }

    public function getParent()
    {
        return FormType::class;
    }
}
