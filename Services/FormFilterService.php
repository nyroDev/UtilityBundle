<?php
namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Form\Form;
use NyroDev\UtilityBundle\Form\Type\FilterTypeInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Service used to update QueryBuilder object regarding a form containing some FilterTypeInterface 
 */
class FormFilterService extends AbstractService {
	
	/**
	 * Build the QueryBuilder object
	 *
	 * @param Form $form
	 * @param QueryBuilder $queryBuilder
	 */
	public function buildQuery(Form $form, $queryBuilder) {
		$data = $form->getData();
		
		foreach($data as $name=>$val) {
			if (isset($val['value']) && $val['value']) {
				$type = $form->get($name)->getConfig()->getType();
				if (is_callable(array($type, 'getInnerType')))
					$type = $type->getInnerType();
				if ($type instanceof FilterTypeInterface) {
					$type->applyFilter($queryBuilder, $name, $data[$name]);
				}
			}
		}
	}
	
	public function fillFromSession(Form $form, $route) {
		$data = $this->get('session')->get('filter_'.$route);
		if (is_array($data))
			$form->submit($data);
	}
	
	public function saveSession(Form $form, $route) {
		$tmp = $form->getData();
		$data = array();
		foreach($tmp as $k=>$v) {
			if (isset($v['value'])) {
				$value = $v['value'];
				if ($value && is_object($value)) {
					$class = get_class($value);
					if ($class == 'DateTime') {
						$value = array(
							'day'=>$value->format('j'),
							'month'=>$value->format('n'),
							'year'=>$value->format('Y'),
						);
					} else if ($class == 'Doctrine\Common\Collections\ArrayCollection') {
						$value = array();
						foreach($value as $vv)
							$value[] = $vv->getId();
					} else {
						$value = $value->getId();
					}
				}
				if ($value)
					$data[$k] = array(
						'transformer'=>$v['transformer'],
						'value'=>$value
					);
			}
		}
		$this->get('session')->set('filter_'.$route, $data);
	}
	
	public function getSessionPage($route) {
		return $this->get('session')->get('filter_'.$route.'_page', 1);
	}
	public function saveSessionPage($route, $page) {
		$this->get('session')->set('filter_'.$route.'_page', $page);
	}
	
	public function getSessionSortOrder($route, $defaults = array()) {
		return $this->get('session')->get('filter_'.$route.'_sortOrder', $defaults);
	}
	public function saveSessionSortOrder($route, $sort, $order) {
		$this->get('session')->set('filter_'.$route.'_sortOrder', array($sort, $order));
	}

	/**
	 * Get parameter from a form for creating a pager URL
	 *
	 * @param Form $form
	 * @return array
	 */
	public function getPrmForUrl(Form $form) {
		$ret = array();
		foreach($form->getData() as $k=>$data) {
			if (
					isset($data['transformer']) && $data['transformer']
				&&  isset($data['value'])
				) {
				if ($data['value'] instanceof \DateTime)
					$data['value'] = array(
						'year'=>intval($data['value']->format('Y')),
						'month'=>intval($data['value']->format('m')),
						'day'=>intval($data['value']->format('d'))
					);
				else if (is_object($data['value'])) {
					if (get_class($data['value']) == 'Doctrine\Common\Collections\ArrayCollection') {
						$value = array();
						foreach($data['value'] as $vv)
							$value[] = $vv->getId();
						$data['value'] = $value;
					} else {
						$data['value'] = $data['value']->getId();
					}
				}
				$ret[$k] = $data;
			}
		}
		return count($ret) ? array($form->getName()=>$ret) : array();
	}

}