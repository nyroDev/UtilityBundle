<?php

namespace NyroDev\UtilityBundle\Services;

use NyroDev\UtilityBundle\Form\Type\DummyCaptchaType;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Service used to handle forms to add more features.
 */
class FormService extends AbstractService
{
    use AssetsPackagesServiceableTrait;

    protected $formFactory;

    public function __construct(
        FormFactory $formFactory,
        ValidatorInterface $validator
    ) {
        $this->formFactory = $formFactory;
        $this->validator = $validator;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function addDummyCaptcha(Form $form)
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

    public function getPluploadAttrs($filters = 'images', $pluploadKey = 'plupload_')
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
            'data-'.$pluploadKey.'swf' => $this->getAssetsPackages()->getUrl('bundles/nyrodevutility/vendor/plupload/Moxie.swf'),
            'data-'.$pluploadKey.'xap' => $this->getAssetsPackages()->getUrl('bundles/nyrodevutility/vendor/plupload/Moxie.xap'),
        ];

        $pluploadMaxFileSize = $this->getParameter('nyroDev_utility.pluploadMaxFileSize');
        if ($pluploadMaxFileSize) {
            $ret['data-'.$pluploadKey.'max_file_size'] = $pluploadMaxFileSize;
        }

        return $ret;
    }
}
