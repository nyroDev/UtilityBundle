<?php

namespace NyroDev\UtilityBundle\QueryBuilder;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\Services\Db\AbstractService;
use Symfony\Component\Serializer\Exception\RuntimeException;

/**
 * Pager utility object.
 */
abstract class AbstractQueryBuilder
{
    const WHERE_OR = '__OR__';
    const WHERE_SUB = '__SUB__';

    const OPERATOR_EQUALS = '=';
    const OPERATOR_NOT_EQUALS = '<>';
    const OPERATOR_GT = '>';
    const OPERATOR_GTE = '>=';
    const OPERATOR_LT = '<';
    const OPERATOR_LTE = '<=';
    const OPERATOR_LIKE = 'LIKE';
    const OPERATOR_LIKEDATE = 'LIKE%';
    const OPERATOR_CONTAINS = 'LIKE %...%';
    const OPERATOR_IN = 'IN';
    const OPERATOR_NOT_IN = 'NOT IN';

    const OPERATOR_IS_NULL = 'IS NULL';
    const OPERATOR_IS_NOT_NULL = 'IS NOT NULL';

    /**
     * @var AbstractService
     */
    protected $service;

    /**
     * @var ObjectRepository
     */
    protected $or;

    /**
     * @var ObjectManager
     */
    protected $om;

    public function __construct(ObjectRepository $or, ObjectManager $om, AbstractService $service)
    {
        $this->or = $or;
        $this->om = $om;
        $this->service = $service;
    }
    
    public function __clone()
    {
        $this->built = null;
        $this->queryBuilder = null;
        $this->count = null;
    }

    protected $config = array();

    public function add($type, $value, $append = false)
    {
        if (!$append || !isset($this->config[$type])) {
            $this->config[$type] = array();
        }
        $this->config[$type][] = $value;

        return $this;
    }

    public function get($type)
    {
        return isset($this->config[$type]) ? $this->config[$type] : null;
    }

    public function addJoinWhere($table, $whereId, $subSelectField = 'id')
    {
        if (!is_array($whereId)) {
            $whereId = array($whereId);
        }

        return $this->add('joinWhere', array($table, $whereId, $subSelectField), true);
    }

    public function addWhere($field, $transformer, $value = null, $forceType = null)
    {
        return $this->add('where', array($field, $transformer, $value, $forceType), true);
    }

    public function orderBy($sort, $order = null)
    {
        return $this->add('orderBy', array($sort, $order));
    }

    public function addOrderBy($sort, $order = null)
    {
        return $this->add('orderBy', array($sort, $order), true);
    }

    public function setFirstResult($firstResult)
    {
        $this->config['firstResult'] = $firstResult;

        return $this;
    }

    public function setMaxResults($maxResults)
    {
        $this->config['maxResults'] = $maxResults;

        return $this;
    }

    protected $built;
    protected $queryBuilder;
    protected $count;
    protected function buildRealQueryBuilder()
    {
        if ($this->built) {
            throw new RuntimeException('NyroDev\UtilityBundle\Utility\QueryBuilder can be built only once.');
        }

        $this->queryBuilder = $this->_buildRealQueryBuilder();

        if (is_null($this->queryBuilder)) {
            throw new RuntimeException(get_class($this).' did not built query builder correctly.');
        }

        $this->built = true;
    }

    protected function _buildRealQueryBuilder()
    {
        return $this->getNewQueryBuilder();
    }

    /**
     * @param bool $complete
     *
     * @return Specific query builder
     */
    abstract public function getNewQueryBuilder($complete = false);

    public function getQueryBuilder()
    {
        if (!$this->built) {
            $this->buildRealQueryBuilder();
        }

        return $this->queryBuilder;
    }

    public function getQuery()
    {
        return $this->getQueryBuilder()->getQuery();
    }

    abstract public function getResult();

    public function count()
    {
        if (is_null($this->count)) {
            $this->count = $this->_count();
        }

        return $this->count;
    }

    abstract protected function _count();
}
