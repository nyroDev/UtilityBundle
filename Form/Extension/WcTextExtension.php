<?php

namespace NyroDev\UtilityBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WcTextExtension extends AbstractTypeExtension
{
    /**
     * Returns an array of extended types.
     */
    public static function getExtendedTypes(): iterable
    {
        return [TextType::class];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['wc']) {
            $view->vars['wc_text'] = $options['wc'];
            $view->vars['wc_html'] = $options['wcHtml'] ?? null;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'wc' => false,
            'wcHtml' => null,
        ]);
    }
}
