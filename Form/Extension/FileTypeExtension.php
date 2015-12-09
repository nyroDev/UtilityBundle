<?php

namespace NyroDev\UtilityBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType;

class FileTypeExtension extends AbstractTypeExtension {

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType() {
        return FileType::class;
    }

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'showCurrent'=>true,
			'showDelete'=>false,
		));
	}
	
    /**
     * Pass the image URL to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options) {
		$data = $form->getParent()->getData();
		if ($data instanceof AbstractUploadable) {
			$currentFile = $data->getWebPath($form->getName());
			if ($currentFile) {
				$view->vars['currentFile'] = $currentFile;
				$view->vars['showDelete'] = $options['showDelete'] && is_string($options['showDelete']) ? $options['showDelete'] : false;
			}
		}
    }

}