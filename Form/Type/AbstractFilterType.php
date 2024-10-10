<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('get')
            ->add('submit', SubmitType::class, ['label' => $this->trans('admin.misc.filter')]);
    }
}
