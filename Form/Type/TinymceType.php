<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TinymceType extends AbstractType {
	
	public function buildView(FormView $view, FormInterface $form, array $options) {
		$attrs = $view->vars['attr'];
		if (!is_array($attrs))
			$attrs = array();
		
		$prefixTinymce = 'data-tinymce_';
		
		$attrs = array_merge($attrs, array(
			'class'=>'tinymce',
			'data-tinymceurl'=>$this->container->get('templating.helper.assets')->getUrl('bundles/nyrodevutility/vendor/tinymce/tinymce.min.js'),
			$prefixTinymce.'language'=>$this->container->get('request')->getLocale(),
			$prefixTinymce.'height'=>450,
			$prefixTinymce.'width'=>720,
			$prefixTinymce.'theme'=>'modern',
			$prefixTinymce.'plugins'=>'lists,advlist,anchor,autolink,link,image,charmap,preview,hr,searchreplace,visualblocks,visualchars,code,fullscreen,insertdatetime,media,nonbreaking,table,paste,contextmenu,tabfocus,wordcount'.(isset($options['tinymcePlugins']) && $options['tinymcePlugins'] ? ','.$options['tinymcePlugins'] : null),
			$prefixTinymce.'toolbar'=>'undo redo | styleselect | bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media fullpage',
			$prefixTinymce.'menubar'=>'insert edit view table tools',
			$prefixTinymce.'relative_urls'=>false,
		));
		
		if ((isset($options['tinymceBrowser']) && $options['tinymceBrowser']) || ($this->container->hasParameter('nyrodev_utility.browser.defaultEnable') && $this->container->getParameter('nyrodev_utility.browser.defaultEnable'))) {
			$canBrowse = isset($options['tinymceBrowser']['url']) || ($this->container->hasParameter('nyrodev_utility.browser.defaultRoute') && $this->container->getParameter('nyrodev_utility.browser.defaultRoute'));
			if ($canBrowse) {
				$attrs[$prefixTinymce.'plugins'].= ',responsivefilemanager';
				
				$attrs[$prefixTinymce.'external_filemanager_path'] = (isset($options['tinymceBrowser']['url']) ? $options['tinymceBrowser']['url'] : $this->container->get('nyrodev')->generateUrl($this->container->getParameter('nyrodev_utility.browser.defaultRoute'))).'/';
				$attrs[$prefixTinymce.'filemanager_title'] = isset($options['tinymceBrowser']['title']) ? $options['tinymceBrowser']['title'] : $this->container->get('translator')->trans('nyrodev.browser.title');
				$attrs[$prefixTinymce.'external_plugins'] = json_encode(array('filemanager'=>$attrs[$prefixTinymce.'external_filemanager_path'].'plugin.min.js'));
				
				/*
				$attrs['data-browser_url'] = isset($options['tinymceBrowser']['url']) ? $options['tinymceBrowser']['url'] : $this->container->get('nyrodev')->generateUrl($this->container->getParameter('nyrodev_utility.tinymce.browserRoute'));
				$attrs['data-browser_width'] = isset($options['tinymceBrowser']['width']) ? $options['tinymceBrowser']['width'] : 800;
				$attrs['data-browser_height'] = isset($options['tinymceBrowser']['height']) ? $options['tinymceBrowser']['height'] : 600;
				$attrs['data-browser_title'] = isset($options['tinymceBrowser']['title']) ? $options['tinymceBrowser']['title'] : $this->container->get('translator')->trans('nyrodev.browser.title');
				 */
			}
		}
		
		if (isset($options['tinymce']) && is_array($options['tinymce'])) {
			foreach($options['tinymce'] as $k=>$v)
				$attrs[$prefixTinymce.$k] = $v;
		}
		
		$view->vars['attr'] = $attrs;
	}
		
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'tinymceBrowser'=>array(),
			'tinymce'=>array(),
			'tinymcePlugins'=>null
		));
    }
	
	public function getParent() {
		return 'textarea';
	}
	
	public function getName() {
		return 'tinymce';
	}
}