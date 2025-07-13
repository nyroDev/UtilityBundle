<?php

namespace NyroDev\UtilityBundle\Form\Extension;

use Exception;
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'wc' => false,
            'wcChoose' => false,
            'wcChooseIcon' => false,
            'wcDelete' => false,
            'wcDeleteIcon' => false,
            'currentFile' => false,
            'currentFileName' => false,
            'currentFileUrl' => false,
            'showCurrent' => true,
            'showDelete' => false,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $data = $form->getParent()->getData();
        if ($options['wc']) {
            $view->vars['wc_file'] = true === $options['wc'] ? 'nyro-file' : $options['wc'];
            if ($options['wcChoose']) {
                $view->vars['wc_choose'] = $options['wcChoose'];
            } elseif ($options['wcChooseIcon']) {
                $view->vars['wc_choose_icon'] = $options['wcChooseIcon'];
            }
            if ($options['wcDelete']) {
                $view->vars['wc_delete'] = $options['wcDelete'];
            } elseif ($options['wcDeleteIcon']) {
                $view->vars['wc_delete_icon'] = $options['wcDeleteIcon'];
            }
            if ($options['showDelete'] && is_string($options['showDelete'])) {
                if (isset($view->vars['attr'])) {
                    $view->vars['attr'] = [];
                }
                $view->vars['attr']['name-delete'] = $options['showDelete'];
            }
        }
        if ($options['currentFile'] || $data instanceof AbstractUploadable) {
            try {
                $currentFile = isset($options['currentFile']) && $options['currentFile'] ? $options['currentFile'] : $data->getWebPath($form->getName());
            } catch (Exception $e) {
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
