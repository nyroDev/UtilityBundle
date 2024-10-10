<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\AbstractType as SrcAbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DummyCaptchaType extends SrcAbstractType
{
    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'dummy_captcha';
    }
}
