<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\AbstractType as SrcAbstractType;

class DummyCaptchaType extends SrcAbstractType {
	
	public function getParent() {
		return 'text';
	}
	
	public function getName() {
		return 'dummy_captcha';
	}

}