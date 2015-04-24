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
	
	public function getPluploadAttrs($filters = 'images') {
		if ($filters == 'images') {
			$filters = array(
				array(
					'title'=>$this->trans('nyrodev.plupload.images'),
					'extensions'=>'jpg,jpeg,gif,png'
				)
			);
		}
		return array(
			'class'=>'pluploadInit',
			'data-plupload_browse'=>$this->trans('nyrodev.plupload.browse'),
			'data-plupload_waiting'=>$this->trans('nyrodev.plupload.waiting'),
			'data-plupload_error'=>$this->trans('nyrodev.plupload.error'),
			'data-plupload_cancel'=>$this->trans('nyrodev.plupload.cancel'),
			'data-plupload_complete'=>$this->trans('nyrodev.plupload.complete'),
			'data-plupload_cancelall'=>$this->trans('nyrodev.plupload.cancelAll'),
			'data-plupload_filters'=>json_encode($filters),
			'data-plupload_swf'=>$this->get('templating.helper.assets')->getUrl('bundles/nyrodevutility/vendor/plupload/Moxie.swf'),
			'data-plupload_xap'=>$this->get('templating.helper.assets')->getUrl('bundles/nyrodevutility/vendor/plupload/Moxie.xap'),
		);
	}

}