<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\Validator\Constraints\ValidConfig;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function ($original) {
                    return is_array($original) ? json_encode($original) : $original;
                },
                function ($submitted) {
                    $ret = null;
                    if ($submitted) {
                        $ret = json_decode($submitted, true);
                        if (!is_array($ret)) {
                            $ret = false;
                        }
                    }

                    return $ret;
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver
            ->setDefault('constraints', [
                new ValidConfig(),
            ]);
    }

    public function getParent(): ?string
    {
        return TextareaType::class;
    }
}
