<?php

namespace NyroDev\UtilityBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmitExtension extends AbstractTypeExtension
{
    /**
     * Returns an array of extended types.
     */
    public static function getExtendedTypes(): iterable
    {
        return [SubmitType::class];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['cancelUrl']) {
            $view->vars['cancelUrl'] = $options['cancelUrl'];
            $view->vars['cancelText'] = $options['cancelText'] ?? 'admin.misc.cancel';
            if ($options['cancelIcon']) {
                $view->vars['cancelIcon'] = $options['cancelIcon'];
            }
            $view->vars['cancelClass'] = $options['cancelClass'] ?? 'cancel';
        }
        if ($options['buttonHtml']) {
            $view->vars['buttonHtml'] = $options['buttonHtml'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'cancelUrl' => null,
            'cancelText' => null,
            'cancelIcon' => null,
            'cancelClass' => null,
            'buttonHtml' => null,
        ]);
    }
}
