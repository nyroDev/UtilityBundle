<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RawHtmlType extends AbstractType
{
    public function getParent()
    {
        return TextareaType::class;
    }

    public function getBlockPrefix()
    {
        return 'rawhtml';
    }
}
