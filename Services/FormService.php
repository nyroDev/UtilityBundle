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

    public function getPluploadAttrs(string $filters = 'images', string $pluploadKey = 'plupload_'): array
    {
        if ('images' == $filters) {
            $filters = [
                [
                    'title' => $this->trans('nyrodev.plupload.images'),
                    'extensions' => 'jpg,jpeg,gif,png',
                ],
            ];
        }

        $ret = [
            'class' => 'pluploadInit',
            'data-'.$pluploadKey.'browse' => $this->trans('nyrodev.plupload.browse'),
            'data-'.$pluploadKey.'waiting' => $this->trans('nyrodev.plupload.waiting'),
            'data-'.$pluploadKey.'error' => $this->trans('nyrodev.plupload.error'),
            'data-'.$pluploadKey.'cancel' => $this->trans('nyrodev.plupload.cancel'),
            'data-'.$pluploadKey.'complete' => $this->trans('nyrodev.plupload.complete'),
            'data-'.$pluploadKey.'cancelall' => $this->trans('nyrodev.plupload.cancelAll'),
            'data-'.$pluploadKey.'filters' => json_encode($filters),
            'data-'.$pluploadKey.'swf' => $this->getAssetsPackages()->getUrl('plupload/Moxie.swf'),
            'data-'.$pluploadKey.'xap' => $this->getAssetsPackages()->getUrl('plupload/Moxie.xap'),
        ];

        $pluploadMaxFileSize = $this->getParameter('nyroDev_utility.pluploadMaxFileSize');
        if ($pluploadMaxFileSize) {
            $ret['data-'.$pluploadKey.'max_file_size'] = $pluploadMaxFileSize;
        }

        return $ret;
    }
}
