<?php
namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Form\Form;
use NyroDev\UtilityBundle\Form\Type\DummyCaptchaType;

/**
 * Service used to handle forms to add more features
 */
class FormService extends AbstractService {
	
	public function addDummyCaptcha(Form $form) {
		$form->add('dummytcha', DummyCaptchaType::class, array(
			'mapped'=>false,
			'required'=>false,
			'position'=>'first',
			'constraints'=>array(
				new \Symfony\Component\Validator\Constraints\Blank()
			)
		));
	}
	
	public function getPluploadAttrs($filters = 'images', $pluploadKey = 'plupload_') {
		if ($filters == 'images') {
			$filters = array(
				array(
					'title'=>$this->trans('nyrodev.plupload.images'),
					'extensions'=>'jpg,jpeg,gif,png'
				)
			);
		}
		
		$ret = array(
			'class'=>'pluploadInit',
			'data-'.$pluploadKey.'browse'=>$this->trans('nyrodev.plupload.browse'),
			'data-'.$pluploadKey.'waiting'=>$this->trans('nyrodev.plupload.waiting'),
			'data-'.$pluploadKey.'error'=>$this->trans('nyrodev.plupload.error'),
			'data-'.$pluploadKey.'cancel'=>$this->trans('nyrodev.plupload.cancel'),
			'data-'.$pluploadKey.'complete'=>$this->trans('nyrodev.plupload.complete'),
			'data-'.$pluploadKey.'cancelall'=>$this->trans('nyrodev.plupload.cancelAll'),
			'data-'.$pluploadKey.'filters'=>json_encode($filters),
			'data-'.$pluploadKey.'swf'=>$this->get('templating.helper.assets')->getUrl('bundles/nyrodevutility/vendor/plupload/Moxie.swf'),
			'data-'.$pluploadKey.'xap'=>$this->get('templating.helper.assets')->getUrl('bundles/nyrodevutility/vendor/plupload/Moxie.xap'),
		);
		
		$pluploadMaxFileSize = $this->getParameter('nyroDev_utility.pluploadMaxFileSize');
		if ($pluploadMaxFileSize)
			$ret['data-'.$pluploadKey.'max_file_size'] = $pluploadMaxFileSize;
		
		return $ret;
	}

}