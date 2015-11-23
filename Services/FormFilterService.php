<?php
namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Form\Form;
use NyroDev\UtilityBundle\Form\Type\FilterTypeInterface;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;

/**
 * Service used to update AbstractQueryBuilder object regarding a form containing some FilterTypeInterface 
 */
class FormFilterService extends AbstractService {
	
	/**
	 * Build the QueryBuilder object
	 *
	 * @param Form $form
	 * @param AbstractQueryBuilder $queryBuilder
	 */
	public function buildQuery(Form $form, AbstractQueryBuilder $queryBuilder) {
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
				$value = $this->prepareValueForSession($v['value']);
				if ($value)
					$data[$k] = array_filter(array(
						'transformer'=>isset($v['transformer']) ? $v['transformer'] : null,
						'value'=>$value
					));
			}
		}
		$this->get('session')->set('filter_'.$route, $data);
	}
	
	protected function prepareValueForSession($value) {
		if (is_object($value)) {
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
		} else if ($value && is_array($value) && (isset($value['start']) || isset($value['end']))) {
			$value['start'] = $this->prepareValueForSession($value['start']);
			$value['end'] = $this->prepareValueForSession($value['end']);
		}
		return $value;
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
			if (isset($data['value'])) {
				$data['value'] = $this->prepareDataForUrl($data['value']);
				$ret[$k] = $data;
			}
		}
		return count($ret) ? array($form->getName()=>$ret) : array();
	}
	
	protected function prepareDataForUrl($value) {
		if ($value) {
			if ($value instanceof \DateTime) {
				$value = array(
					'year'=>intval($value->format('Y')),
					'month'=>intval($value->format('m')),
					'day'=>intval($value->format('d'))
				);
			} else if (is_object($value)) {
				if (get_class($value) == 'Doctrine\Common\Collections\ArrayCollection') {
					$tmp = array();
					foreach($value as $vv)
						$tmp[] = $vv->getId();
					$value = $tmp;
				} else {
					$value = $value->getId();
				}
			} else if (is_array($value) && (isset($value['start']) || isset($value['end']))) {
				$value['start'] = $this->prepareDataForUrl($value['start']);
				$value['end'] = $this->prepareDataForUrl($value['end']);
			}
		}
		return $value;
	}

}