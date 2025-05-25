<?php

namespace NyroDev\UtilityBundle\Form\Extension;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WcSelectExtension extends AbstractTypeExtension
{
    /**
     * Returns an array of extended types.
     */
    public static function getExtendedTypes(): iterable
    {
        return [EntityType::class, ChoiceType::class];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['wc']) {
            if (!isset($view->vars['wc_select']) && $view->vars['expanded']) {
                $view->vars['full_name'] .= '[]';
            }
            $view->vars['wc_select'] = true === $options['wc'] ? 'nyro-select' : $options['wc'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'wc' => false,
        ]);
    }
}
