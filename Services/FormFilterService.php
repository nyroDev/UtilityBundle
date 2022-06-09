<?php

namespace NyroDev\UtilityBundle\Services;

use NyroDev\UtilityBundle\Form\Type\FilterTypeInterface;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\Form;

/**
 * Service used to update AbstractQueryBuilder object regarding a form containing some FilterTypeInterface.
 */
class FormFilterService extends AbstractService
{
    /**
     * Build the QueryBuilder object.
     */
    public function buildQuery(Form $form, AbstractQueryBuilder $queryBuilder)
    {
        $data = $form->getData();

        foreach ($data as $name => $val) {
            if (isset($val['value']) && $val['value']) {
                $type = $form->get($name)->getConfig()->getType();
                if (is_callable([$type, 'getInnerType'])) {
                    $type = $type->getInnerType();
                }
                if ($type instanceof FilterTypeInterface) {
                    $type->applyFilter($queryBuilder, $name, $data[$name]);
                }
            }
        }
    }

    public function fillFromSession(Form $form, $route)
    {
        $filled = false;
        $data = $this->get('request_stack')->getSession()->get('filter_'.$route);
        if (is_array($data) && count($data)) {
            $form->submit($data);
            $filled = true;
        }

        return $filled;
    }

    public function saveSession(Form $form, $route)
    {
        $tmp = $form->getData();
        $data = [];
        foreach ($tmp as $k => $v) {
            if (isset($v['value'])) {
                $value = $this->prepareValueForSession($v['value'], $form->get($k)->get('value'));
                if ($value) {
                    $data[$k] = array_filter([
                        'transformer' => isset($v['transformer']) ? $v['transformer'] : null,
                        'value' => $value,
                    ]);
                }
            }
        }
        $this->get('request_stack')->getSession()->set('filter_'.$route, $data);
    }

    public function prepareValueForSession($value, Form $form)
    {
        if (is_object($value)) {
            $class = get_class($value);
            if ('DateTime' == $class) {
                $value = $form->getViewData();
            } elseif ('Doctrine\Common\Collections\ArrayCollection' == $class) {
                $values = [];
                foreach ($value as $vv) {
                    $values[] = $vv->getId();
                }
                $value = $values;
            } else {
                $value = $value->getId();
            }
        } elseif ($value && is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->prepareValueForSession($v, $form->get($k));
            }
        }

        return $value;
    }

    public function getSessionPage($route)
    {
        return $this->get('request_stack')->getSession()->get('filter_'.$route.'_page', 1);
    }

    public function saveSessionPage($route, $page)
    {
        $this->get('request_stack')->getSession()->set('filter_'.$route.'_page', $page);
    }

    public function getSessionSortOrder($route, $defaults = [])
    {
        return $this->get('request_stack')->getSession()->get('filter_'.$route.'_sortOrder', $defaults);
    }

    public function saveSessionSortOrder($route, $sort, $order)
    {
        $this->get('request_stack')->getSession()->set('filter_'.$route.'_sortOrder', [$sort, $order]);
    }

    /**
     * Get parameter from a form for creating a pager URL.
     *
     * @return array
     */
    public function getPrmForUrl(Form $form)
    {
        $ret = [];
        foreach ($form->getData() as $k => $data) {
            if (isset($data['value'])) {
                $data['value'] = $this->prepareDataForUrl($data['value'], $form->get($k)->get('value'));
                $ret[$k] = $data;
            }
        }

        return count($ret) ? [$form->getName() => $ret] : [];
    }

    protected function prepareDataForUrl($value, Form $form)
    {
        if ($value) {
            if ($value instanceof \DateTime) {
                $value = $form->getViewData();
            } elseif (is_object($value)) {
                if ('Doctrine\Common\Collections\ArrayCollection' == get_class($value)) {
                    $tmp = [];
                    foreach ($value as $vv) {
                        $tmp[] = $vv->getId();
                    }
                    $value = $tmp;
                } else {
                    $value = $value->getId();
                }
            } elseif (is_array($value) && (isset($value['start']) || isset($value['end']))) {
                $value['start'] = $this->prepareDataForUrl($value['start'], $form->get('start'));
                $value['end'] = $this->prepareDataForUrl($value['end'], $form->get('end'));
            }
        }

        return $value;
    }
}
