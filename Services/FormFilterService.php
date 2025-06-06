<?php

namespace NyroDev\UtilityBundle\Services;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use NyroDev\UtilityBundle\Form\Type\FilterTypeInterface;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\Form\FormInterface;

/**
 * Service used to update AbstractQueryBuilder object regarding a form containing some FilterTypeInterface.
 */
class FormFilterService extends AbstractService
{
    public function buildQuery(FormInterface $form, AbstractQueryBuilder $queryBuilder): void
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

    public function fillFromSession(FormInterface $form, string $route): bool
    {
        $filled = false;
        $data = $this->get('request_stack')->getSession()->get('filter_'.$route);
        if (is_array($data) && count($data)) {
            $form->submit($data);
            $filled = true;
        }

        return $filled;
    }

    public function saveSession(FormInterface $form, string $route): void
    {
        $this->get('request_stack')->getSession()->set('filter_'.$route, $this->prepareValuesForSession($form));
    }

    public function prepareValuesForSession(FormInterface $form): array
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

        return $data;
    }

    public function prepareValueForSession(mixed $value, FormInterface $form): mixed
    {
        if (is_object($value)) {
            if ($value instanceof DateTimeInterface) {
                $value = $form->getViewData();
            } elseif ($value instanceof Collection) {
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

    public function getSessionPage(string $route): int
    {
        return $this->get('request_stack')->getSession()->getInt('filter_'.$route.'_page', 1);
    }

    public function saveSessionPage(string $route, int $page): void
    {
        $this->get('request_stack')->getSession()->set('filter_'.$route.'_page', $page);
    }

    public function getSessionSortOrder(string $route, array $defaults = []): mixed
    {
        return $this->get('request_stack')->getSession()->get('filter_'.$route.'_sortOrder', $defaults);
    }

    public function saveSessionSortOrder(string $route, mixed $sort, mixed $order): void
    {
        $this->get('request_stack')->getSession()->set('filter_'.$route.'_sortOrder', [$sort, $order]);
    }

    /**
     * Get parameter from a form for creating a pager URL.
     */
    public function getPrmForUrl(FormInterface $form): array
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

    protected function prepareDataForUrl(mixed $value, FormInterface $form): mixed
    {
        if ($value) {
            if ($value instanceof DateTimeInterface) {
                $value = $form->getViewData();
            } elseif (is_object($value)) {
                if ($value instanceof Collection) {
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
