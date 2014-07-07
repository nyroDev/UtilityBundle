<?php
namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Form\Form;

/**
 * Service used to handle forms to add more features
 */
class FormService extends AbstractService {
	
	public function addDummyCaptcha(Form $form) {
		$form->add('dummytcha', 'dummy_captcha', array(
			'mapped'=>false,
			'required'=>false,
			'position'=>'first',
			'constraints'=>array(
				new \Symfony\Component\Validator\Constraints\Blank()
			)
		));
	}

}