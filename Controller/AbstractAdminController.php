<?php

namespace NyroDev\UtilityBundle\Controller;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ObjectManager;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\FormFilterService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Utility\PhpSpreadsheetResponse;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractAdminController extends AbstractController
{
    public const ADD = 'add';
    public const EDIT = 'edit';

    protected function createList(
        Request $request,
        $repository,
        string $route,
        array $routePrm = [],
        string $defaultSort = 'id',
        string $defaultOrder = 'desc',
        FormInterface|string|null $filterType = null,
        ?AbstractQueryBuilder $queryBuilder = null,
        ?array $exportConfig = null,
        array $filterDefaults = [],
    ): Response|array {
        $nbPerPageParam = 'admin.nbPerPage.'.$route;
        $nbPerPage = $this->getParameter($nbPerPageParam, $this->getParameter('nyroDev_utility.admin.nbPerPage'));

        $tmpList = $this->getListElements($request, $repository, $route, $defaultSort, $defaultOrder, $filterType, $queryBuilder, $filterDefaults);
        $order = $tmpList['order'];
        $sort = $tmpList['sort'];
        $filter = $tmpList['filter'];
        $filterFilled = $tmpList['filterFilled'];
        $page = $tmpList['page'];
        $queryBuilder = $tmpList['queryBuilder'];
        $rawQueryBuilder = clone $queryBuilder;
        $total = $tmpList['total'];

        $canExport = $exportConfig && is_array($exportConfig) && isset($exportConfig['fields']);
        if ($canExport && $request->query->get('export')) {
            // Start XLS export
            $this->get(NyrodevService::class)->increasePhpLimits();
            $spreadsheet = new Spreadsheet();
            $title = isset($exportConfig['title']) ? $exportConfig['title'] : 'Export';
            $creator = isset($exportConfig['creator']) ? $exportConfig['creator'] : 'Export';
            $spreadsheet->getProperties()->setCreator($creator)
                             ->setLastModifiedBy($creator)
                             ->setTitle($title)
                             ->setSubject($title);
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle($title);

            $row = 1;
            $col = 1;
            foreach ($exportConfig['fields'] as $field) {
                $fieldName = isset($exportConfig['prefix']) ? $this->trans('admin.'.$exportConfig['prefix'].'.'.$field) : $field;
                $worksheet->setCellValue([$col, $row], $fieldName);
                $worksheet->getStyle([$col, $row])->getFont()->setBold(true);
                $worksheet->getColumnDimensionByColumn($col)->setAutoSize(true);
                if (isset($exportConfig['doubleFirstRows']) && $exportConfig['doubleFirstRows']) {
                    $worksheet->setCellValue([$row + 1, $col], $fieldName);
                }
                ++$col;
            }
            if (isset($exportConfig['callbackHeader']) && $exportConfig['callbackHeader']) {
                $fct = $exportConfig['callbackHeader'];
                $this->$fct($tmpList, $worksheet, $row, $col, $exportConfig);
            }
            ++$row;
            if (isset($exportConfig['doubleFirstRows']) && $exportConfig['doubleFirstRows']) {
                ++$row;
            }

            $results = $queryBuilder->getResult();

            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($results as $r) {
                $col = 1;
                foreach ($exportConfig['fields'] as $field) {
                    $val = $accessor->getValue($r, $field);
                    if (is_object($val)) {
                        if ($val instanceof DateTimeInterface) {
                            $val = strftime($this->trans('date.short'), $val->getTimestamp());
                        } elseif ($val instanceof Collection) {
                            $val = $this->get(NyrodevService::class)->joinRows($val);
                        } else {
                            $val = $val.'';
                        }
                    } elseif (isset($exportConfig['boolFields']) && isset($exportConfig['boolFields'][$field]) && $exportConfig['boolFields'][$field]) {
                        $val = $this->trans('admin.misc.'.($val ? 'yes' : 'no'));
                    }
                    $worksheet->setCellValueExplicit([$col, $row], $val, DataType::TYPE_STRING);
                    ++$col;
                }
                if (isset($exportConfig['callbackLine']) && $exportConfig['callbackLine']) {
                    $fct = $exportConfig['callbackLine'];
                    $this->$fct($tmpList, $r, $worksheet, $row, $col, $exportConfig);
                }
                ++$row;
            }

            $worksheet->calculateColumnWidths();

            $filename = isset($exportConfig['filename']) ? $exportConfig['filename'] : (isset($exportConfig['prefix']) ? $exportConfig['prefix'] : 'export');

            $response = new PhpSpreadsheetResponse();
            $response->setPhpSpreadsheet($filename.'.ods', $spreadsheet);

            return $response;
        }

        $routePrm = array_merge($routePrm, ['sort' => $sort, 'order' => $order]);
        if (!is_null($filter)) {
            $routePrm = array_merge($routePrm, $this->get(FormFilterService::class)->getPrmForUrl($filter));
        }
        $pager = $this->get(NyrodevService::class)->getPager($route, $routePrm, $total, $page, $nbPerPage);

        $results = $queryBuilder
                        ->setFirstResult($pager->getStart())
                        ->setMaxResults($nbPerPage)
                        ->getResult();

        return [
            'filter' => !is_null($filter) ? $filter->createView() : null,
            'filterFilled' => $filterFilled,
            'pager' => $pager,
            'routeName' => $route,
            'routePrm' => $routePrm,
            'page' => $page,
            'order' => $order,
            'sort' => $sort,
            'total' => $total,
            'results' => $results,
            'queryBuilder' => $rawQueryBuilder,
            'canExport' => $canExport,
        ];
    }

    protected function getDefaultFilterOptions(): array
    {
        return [
            'csrf_protection' => false,
            'validation_groups' => false,
            'allow_extra_fields' => true,
            'attr' => [
                'class' => 'filterForm',
            ],
        ];
    }

    protected function getListElements(
        Request $request,
        $repository,
        string $route,
        string $defaultSort = 'id',
        string $defaultOrder = 'desc',
        FormInterface|string|null $filterType = null,
        ?AbstractQueryBuilder $queryBuilder = null,
        array $filterDefaults = [],
    ): array {
        $filter = null;
        if (!is_null($filterType)) {
            if ($filterType instanceof FormInterface) {
                // if we have a form object, use it directly
                $filter = $filterType;
            } elseif (is_string($filterType)) {
                $filter = $this->get('nyrodev_form')->getFormFactory()->create($filterType, $filterDefaults, $this->getDefaultFilterOptions());
            }
        }

        $page = $request->query->get('page', $request->getSession()->get('admin_list_'.$route.'_page', 1));
        if (!$page) {
            $page = 1;
        }
        $request->getSession()->set('admin_list_'.$route.'_page', $page);
        $sort = $request->query->get('sort', $request->getSession()->get('admin_list_'.$route.'_sort', $defaultSort));
        $request->getSession()->set('admin_list_'.$route.'_sort', $sort);
        $order = $request->query->get('order', $request->getSession()->get('admin_list_'.$route.'_order', $defaultOrder));
        $request->getSession()->set('admin_list_'.$route.'_order', $order);

        $filterFilled = false;
        if (!is_null($filter)) {
            // bind values from the request
            if ($request->query->has('clearFilter')) {
                $filter->submit(['page' => 1]);
                $this->get(FormFilterService::class)->saveSession($filter, $route);
            } elseif ($request->query->has($filter->getName())) {
                $filter->handleRequest($request);
                $this->get(FormFilterService::class)->saveSession($filter, $route);
                $tmp = $request->query->all($filter->getName());
                if (isset($tmp['submit'])) {
                    $page = 1;
                }
                $filterFilled = true;
            } else {
                $filterFilled = $this->get(FormFilterService::class)->fillFromSession($filter, $route);
            }
        }

        if (is_string($repository)) {
            $repository = $this->get(DbAbstractService::class)->getRepository($repository);
        }

        if (is_null($queryBuilder)) {
            // initialize a query builder
            $queryBuilder = $this->get(DbAbstractService::class)->getQueryBuilder($repository);
        }

        if (!is_null($filter)) {
            // build the query from the given form object
            $this->get(FormFilterService::class)->buildQuery($filter, $queryBuilder);
        }

        $queryBuilder->orderBy($sort, $order);

        // Retrieve the number of total results
        $total = $queryBuilder->count();

        return [
            'order' => $order,
            'sort' => $sort,
            'filter' => $filter,
            'filterFilled' => $filterFilled,
            'page' => $page,
            'queryBuilder' => $queryBuilder,
            'total' => $total,
        ];
    }

    protected function createAdminForm(
        Request $request,
        string $name,
        string $action,
        object $row,
        array $fields,
        string $route,
        array $routePrm = [],
        ?string $callbackForm = null,
        ?string $callbackFlush = null,
        ?array $groups = null,
        array $moreOptions = [],
        ?string $callbackAfterFlush = null,
        ?ObjectManager $objectManager = null,
        array $moreFormOptions = [],
        ?string $formName = null,
    ): Response|array {
        if (is_null($groups)) {
            $groups = ['Default', $action];
        }

        $form = $this->get('nyrodev_form')->getFormFactory()->createNamedBuilder($formName ? $formName : 'form', FormType::class, $row, array_merge($moreFormOptions, [
            'validation_groups' => $groups,
        ]));

        if (self::ADD != $action && $this->getParameter('nyroDev_utility.show_edit_id')) {
            $form->add('id', TextType::class, ['label' => $this->trans('admin.'.$name.'.id'), 'attr' => ['readonly' => 'readonly'], 'mapped' => false]);
            $form->get('id')->setData($row->getId());
        }

        $classMetadata = $this->get('nyrodev_form')->getValidator()->getMetadataFor(get_class($row));

        foreach ($fields as $f) {
            $type = null;
            $options = [
                'label' => $this->trans('admin.'.$name.'.'.$f),
                'required' => false,
            ];

            if (isset($moreOptions[$f])) {
                if (isset($moreOptions[$f]['useType'])) {
                    $type = $moreOptions[$f]['useType'];
                    unset($moreOptions[$f]['useType']);
                } elseif (isset($moreOptions[$f]['type'])) {
                    $type = $moreOptions[$f]['type'];
                    unset($moreOptions[$f]['type']);
                }
                $options = array_merge($options, $moreOptions[$f]);
            }

            if (SubmitType::class === $type) {
                unset($options['required']);
            }

            if ($classMetadata->hasPropertyMetadata($f)) {
                $memberMetadatas = $classMetadata->getPropertyMetadata($f);
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

        $submitOptions = ['label' => $this->trans('admin.misc.send')];
        if (isset($moreOptions['submit']) && is_array($moreOptions['submit'])) {
            $submitOptions = array_merge($submitOptions, $moreOptions['submit']);
        }
        $form->add('submit', SubmitType::class, $submitOptions);

        if (!is_null($callbackForm)) {
            $tmp = $this->$callbackForm($action, $row, $form);
            if ($tmp && $tmp instanceof Response) {
                return $tmp;
            }
        }

        $form = $form->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!is_null($callbackFlush)) {
                $tmp = $this->$callbackFlush($action, $row, $form);
                if ($tmp && $tmp instanceof Response) {
                    return $tmp;
                }
            }

            if (is_null($objectManager)) {
                $objectManager = $this->get(DbAbstractService::class)->getObjectManager();
            }

            if (self::ADD == $action) {
                $objectManager->persist($row);
            }

            $objectManager->flush();

            $response = $this->redirect($this->generateUrl($route, $routePrm));

            if (!is_null($callbackAfterFlush)) {
                $tmp = $this->$callbackAfterFlush($response, $action, $row);
                if ($tmp && $tmp instanceof Response) {
                    $response = $tmp;
                }
            }

            return $response;
        }

        return [
            'name' => $name,
            'action' => $action,
            'row' => $row,
            'route' => $route,
            'routePrm' => $routePrm,
            'form' => $form->createView(),
        ];
    }
}
