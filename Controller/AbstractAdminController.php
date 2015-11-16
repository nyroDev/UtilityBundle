<?php

namespace NyroDev\UtilityBundle\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use NyroDev\UtilityBundle\Form\Type\AbstractFilterType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractAdminController extends AbstractController {
	
	const ADD = 'add';
	const EDIT = 'edit';

	protected function createList($repository, $route, array $routePrm = array(), $defaultSort = 'id', $defaultOrder = 'desc', AbstractFilterType $filterType = null, QueryBuilder $queryBuilder = null, $exportConfig = false) {
		$nbPerPageParam = 'admin.nbPerPage.'.$route;
		$nbPerPage = $this->container->hasParameter($nbPerPageParam) ?
					$this->container->getParameter($nbPerPageParam) :
					$this->container->getParameter('nyrodev_utility.admin.nbPerPage');
		
		$tmpList = $this->getListElements($repository, $route, $defaultSort, $defaultOrder, $filterType, $queryBuilder);
		$orderBy = $tmpList['orderBy'];
		$order = $tmpList['order'];
		$sort = $tmpList['sort'];
		$filter = $tmpList['filter'];
		$page = $tmpList['page'];
		$queryBuilder = $tmpList['queryBuilder'];
		$total = $tmpList['total'];
		
		$canExport = $exportConfig && is_array($exportConfig) && isset($exportConfig['fields']);
		if ($canExport && $this->getRequest()->query->get('export')) {
			// Start XLS export
			$this->get('nyrodev')->increasePhpLimits();
			$phpExcel = new \PHPExcel();
			$title = isset($exportConfig['title']) ? $exportConfig['title'] : 'Export';
			$creator = isset($exportConfig['creator']) ? $exportConfig['creator'] : 'Export';
			$phpExcel->getProperties()->setCreator($creator)
							 ->setLastModifiedBy($creator)
							 ->setTitle($title)
							 ->setSubject($title);
			$sheet = $phpExcel->setActiveSheetIndex(0);
			$sheet->setTitle($title);
			
			$row = 1;
			$col = 0;
			foreach($exportConfig['fields'] as $field) {
				$fieldName = isset($exportConfig['prefix']) ? $this->trans('admin.'.$exportConfig['prefix'].'.'.$field) : $field;
				$sheet->setCellValueByColumnAndRow($col, $row, $fieldName);
				$sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
				$sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
				if (isset($exportConfig['doubleFirstRows']) && $exportConfig['doubleFirstRows'])
					$sheet->setCellValueByColumnAndRow($col, $row + 1, $fieldName);
				$col++;
			}
			if (isset($exportConfig['callbackHeader']) && $exportConfig['callbackHeader']) {
				$fct = $exportConfig['callbackHeader'];
				$this->$fct($tmpList, $sheet, $row, $col, $exportConfig);
			}
			$row++;
			if (isset($exportConfig['doubleFirstRows']) && $exportConfig['doubleFirstRows'])
				$row++;
			
			$results = $queryBuilder
							->orderBy($orderBy, $order)
							->getQuery()->getResult();
			
			$accessor = PropertyAccess::createPropertyAccessor();
			foreach($results as $r) {
				$col = 0;
				foreach($exportConfig['fields'] as $field) {
					$val = $accessor->getValue($r, $field);
					if (is_object($val)) {
						if ($val instanceof \DateTime) {
							$val = strftime($this->trans('date.short'), $val->getTimestamp());
						} else if ($val instanceof Collection) {
							$val = $this->get('nyrodev')->joinRows($val);
						} else {
							$val = $val.'';
						}
					} else if (isset($exportConfig['boolFields']) && isset($exportConfig['boolFields'][$field]) && $exportConfig['boolFields'][$field]) {
						$val = $this->trans('admin.misc.'.($val ? 'yes' : 'no'));
					}
					$sheet->setCellValueExplicitByColumnAndRow($col, $row, $val, \PHPExcel_Cell_DataType::TYPE_STRING);
					//$sheet->setCellValueByColumnAndRow($col, $row, $val);
					$col++;
				}
				if (isset($exportConfig['callbackLine']) && $exportConfig['callbackLine']) {
					$fct = $exportConfig['callbackLine'];
					$this->$fct($tmpList, $r, $sheet, $row, $col, $exportConfig);
				}
				$row++;
			}
			
			$sheet->calculateColumnWidths();
		
			$filename = isset($exportConfig['filename']) ? $exportConfig['filename'] : (isset($exportConfig['prefix']) ? $exportConfig['prefix'] : 'export');
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
		}
		
		$routePrm = array_merge($routePrm, array('sort'=>$sort, 'order'=>$order));
		if (!is_null($filter))
			$routePrm = array_merge($routePrm, $this->get('nyrodev_formFilter')->getPrmForUrl($filter));
		$pager = $this->get('nyrodev')->getPager($route, $routePrm, $total, $page, $nbPerPage);
		
		$results = $queryBuilder
						->setFirstResult($pager->getStart())
						->setMaxResults($nbPerPage)
						->orderBy($orderBy, $order)
						->getQuery()->getResult();
		
		return array(
			'filter'=>!is_null($filter) ? $filter->createView() : null,
			'pager'=>$pager,
			'routeName'=>$route,
			'routePrm'=>$routePrm,
			'total'=>$total,
			'results'=>$results,
			'canExport'=>$canExport
		);
	}
	
	protected function getListElements($repository, $route, $defaultSort = 'id', $defaultOrder = 'desc', AbstractFilterType $filterType = null, QueryBuilder $queryBuilder = null) {
		$filter = null;
		if (!is_null($filterType))
			$filter = $this->createForm($filterType, array(), array('csrf_protection'=>false, 'attr'=>array('class'=>'filterForm')));
		
		$page = $this->getRequest()->query->get('page', $this->getRequest()->getSession()->get('admin_list_'.$route.'_page', 1));
		if (!$page)
			$page = 1;
		$this->getRequest()->getSession()->set('admin_list_'.$route.'_page', $page);
		$sort = $this->getRequest()->query->get('sort', $this->getRequest()->getSession()->get('admin_list_'.$route.'_sort', $defaultSort));
		$this->getRequest()->getSession()->set('admin_list_'.$route.'_sort', $sort);
		$order = $this->getRequest()->query->get('order', $this->getRequest()->getSession()->get('admin_list_'.$route.'_order', $defaultOrder));
		$this->getRequest()->getSession()->set('admin_list_'.$route.'_order', $order);
		
		if (!is_null($filter)) {
			// bind values from the request
			if ($this->getRequest()->query->has('clearFilter')) {
				$filter->submit(array('page'=>1));
				$this->get('nyrodev_formFilter')->saveSession($filter, $route);
			} else if ($this->getRequest()->query->has($filter->getName())) {
				$filter->handleRequest($this->getRequest());
				$this->get('nyrodev_formFilter')->saveSession($filter, $route);
				$tmp = $this->getRequest()->query->get($filter->getName());
				if (isset($tmp['submit']))
					$page = 1;
			} else {
				$this->get('nyrodev_formFilter')->fillFromSession($filter, $route);
			}
		}
		
		if (is_string($repository))
			$repository = $this->getDoctrine()->getRepository($repository);
		
		if (is_null($queryBuilder)) {
			// initliaze a query builder
			$queryBuilder = $repository->createQueryBuilder('l');
		}

		if (!is_null($filter))
			// build the query from the given form object
			$this->get('nyrodev_formFilter')->buildQuery($filter, $queryBuilder);
		
		$orderBy = $queryBuilder->getRootAlias().'.'.$sort;
		
		// Retrieve the number of total results
		$totalQb = $repository
			->createQueryBuilder('cpt')
				->select('COUNT(cpt.id)')
				->andWhere('cpt.id = ANY('.$queryBuilder->getDQL().')');
		$totalQb->setParameters($queryBuilder->getParameters());
		$total = $totalQb->getQuery()->getSingleScalarResult();
		
		return array(
			'orderBy'=>$orderBy,
			'order'=>$order,
			'sort'=>$sort,
			'filter'=>$filter,
			'page'=>$page,
			'queryBuilder'=>$queryBuilder,
			'total'=>$total,
		);
	}
	
	protected function createAdminForm($name, $action, $row, array $fields, $route, $routePrm = array(), $callbackForm = null, $callbackFlush = null, $groups = null, array $moreOptions = array(), $callbackAfterFlush = null, ObjectManager $objectManager = null) {
		if (is_null($groups))
			$groups = array('Default', $action);
		$form = $this->createFormBuilder($row, array('validation_groups'=>$groups));
		
		if ($action != AbstractAdminController::ADD) {
			$form->add('id', 'text', array('label'=>$this->trans('admin.'.$name.'.id'), 'read_only'=>true, 'mapped'=>false));
			$form->get('id')->setData($row->getId());
		}
		
		$classMetadata = $this->get('validator')->getMetadataFor(get_class($row));
		
		foreach($fields as $f) {
			$type = null;
			$options = array(
				'label'=>$this->trans('admin.'.$name.'.'.$f),
				'required'=>false,
			);
			
			if (isset($moreOptions[$f])) {
				if (isset($moreOptions[$f]['type'])) {
					$type = $moreOptions[$f]['type'];
					unset($moreOptions[$f]['type']);
				}
				$options = array_merge($options, $moreOptions[$f]);
			}
		
			if ($classMetadata->hasMemberMetadatas($f)) {
				$memberMetadatas = $classMetadata->getMemberMetadatas($f);
				foreach ($memberMetadatas as $memberMetadata) {
					$constraints = $memberMetadata->getConstraints();
					foreach ($constraints as $constraint) {
						switch (get_class($constraint)) {
							case 'Symfony\Component\Validator\Constraints\NotNull':
							case 'Symfony\Component\Validator\Constraints\NotBlank':
							case 'Symfony\Component\Validator\Constraints\True':
								// we have a required constraint, check against the group
								$options['required'] = count(array_intersect($groups, $constraint->groups)) > 0;
								break;
						}
					}
				}
			}
			$form->add($f, $type, $options);
		}
		
		$submitOptions = array('label'=>$this->trans('admin.misc.send'));
		if (isset($moreOptions['submit']) && is_array($moreOptions['submit']))
			$submitOptions = array_merge($submitOptions, $moreOptions['submit']);
		$form->add('submit', 'submit', $submitOptions);
		
		if (!is_null($callbackForm)) {
			$tmp = $this->$callbackForm($action, $row, $form);
			if ($tmp && $tmp instanceof Response)
				return $tmp;
		}
		
		$form = $form->getForm();
		
		$form->handleRequest($this->getRequest());
		if ($form->isValid()) {
			if (!is_null($callbackFlush)) {
				$tmp = $this->$callbackFlush($action, $row, $form);
				if ($tmp && $tmp instanceof Response)
					return $tmp;
			}
			
			if (is_null($objectManager))
				$objectManager = $this->getDoctrine()->getManager();
			
			if ($action == AbstractAdminController::ADD)
				$objectManager->persist($row);
			
			$objectManager->flush();
			
			$response = $this->redirect($this->generateUrl($route, $routePrm));
			
			if (!is_null($callbackAfterFlush)) {
				$tmp = $this->$callbackAfterFlush($response, $action, $row);
				if ($tmp && $tmp instanceof Response)
					$response = $tmp;
			}
			
			return $response;
		}
		
		return array(
			'name'=>$name,
			'action'=>$action,
			'row'=>$row,
			'form'=>$form->createView(),
		);
	}
	
}