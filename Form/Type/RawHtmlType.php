<?php
namespace NyroDev\UtilityBundle\Form\Type;

class RawHtmlType extends AbstractType {
	
	public function getParent() {
		return 'textarea';
	}
	
	public function getName() {
		return 'rawhtml';
	}

}