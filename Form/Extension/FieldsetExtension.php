<?php

namespace NyroDev\UtilityBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldsetExtension extends AbstractTypeExtension
{
    /**
     * Returns an array of extended types.
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class, ButtonType::class];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['fieldset'] && is_array($options['fieldset'])) {
            $view->vars['fieldset'] = $options['fieldset'];
        }
        if ($options['formTabs']) {
            $view->vars['formTabs'] = $options['formTabs'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'formTabs' => false,
            'fieldset' => false,
        ]);
    }
}
