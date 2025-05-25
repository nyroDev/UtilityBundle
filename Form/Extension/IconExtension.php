<?php

namespace NyroDev\UtilityBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IconExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [
            SubmitType::class,
            FormType::class,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'icon' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (isset($options['icon']) && $options['icon']) {
            $view->vars['icon'] = $options['icon'];
        }
    }
}
