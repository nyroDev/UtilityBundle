<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RawHtmlType extends AbstractType
{
    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'rawhtml';
    }
}
