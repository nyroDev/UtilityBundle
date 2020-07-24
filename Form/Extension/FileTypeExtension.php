<?php

namespace NyroDev\UtilityBundle\Form\Extension;

use NyroDev\UtilityBundle\Model\AbstractUploadable;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [
            FileType::class,
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'currentFile' => false,
            'currentFileName' => false,
            'currentFileUrl' => false,
            'showCurrent' => true,
            'showDelete' => false,
        ]);
    }

    /**
     * Pass the image URL to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getParent()->getData();
        if ($options['currentFile'] || $data instanceof AbstractUploadable) {
            try {
                $currentFile = isset($options['currentFile']) && $options['currentFile'] ? $options['currentFile'] : $data->getWebPath($form->getName());
            } catch (\Exception $e) {
                // In some cases, getWebPath might throw an exception
                $currentFile = null;
            }

            if ($currentFile) {
                $view->vars['currentFile'] = $currentFile;
                $view->vars['currentFileName'] = basename($currentFile);
                $view->vars['currentFileUrl'] = isset($options['currentFileUrl']) && $options['currentFileUrl'] ? $options['currentFileUrl'] : $currentFile;
                $view->vars['showDelete'] = $options['showDelete'] && is_string($options['showDelete']) ? $options['showDelete'] : false;
            }
        }
    }
}
