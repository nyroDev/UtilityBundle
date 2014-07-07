<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class DummyCaptchaType extends AbstractType {
	
	public function getParent() {
		return 'text';
	}
	
	public function getName() {
		return 'dummy_captcha';
	}
}