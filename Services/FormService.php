<?php

namespace NyroDev\UtilityBundle\Services;

use NyroDev\UtilityBundle\Form\Type\DummyCaptchaType;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Service used to handle forms to add more features.
 */
class FormService extends AbstractService
{
    use AssetsPackagesServiceableTrait;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function getFormFactory(): FormFactoryInterface
    {
        return $this->formFactory;
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function addDummyCaptcha(FormInterface $form): void
    {
        $form->add('dummytcha', DummyCaptchaType::class, [
            'mapped' => false,
            'required' => false,
            'position' => 'first',
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\Blank(),
            ],
        ]);
    }
}
