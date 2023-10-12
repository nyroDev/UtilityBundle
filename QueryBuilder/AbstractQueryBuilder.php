<?php

namespace NyroDev\UtilityBundle\QueryBuilder;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService;
use Symfony\Component\Serializer\Exception\RuntimeException;

/**
 * Pager utility object.
 */
abstract class AbstractQueryBuilder
{
    public const WHERE_OR = '__OR__';
    public const WHERE_SUB = '__SUB__';

    public const OPERATOR_EQUALS = '=';
    public const OPERATOR_NOT_EQUALS = '<>';
    public const OPERATOR_GT = '>';
    public const OPERATOR_GTE = '>=';
    public const OPERATOR_LT = '<';
    public const OPERATOR_LTE = '<=';
    public const OPERATOR_LIKE = 'LIKE';
    public const OPERATOR_LIKEDATE = 'LIKE%';
    public const OPERATOR_CONTAINS = 'LIKE %...%';
    public const OPERATOR_IN = 'IN';
    public const OPERATOR_NOT_IN = 'NOT IN';

    public const OPERATOR_IS_NULL = 'IS NULL';
    public const OPERATOR_IS_NOT_NULL = 'IS NOT NULL';

    /**
     * @var DbAbstractService
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

    public function __construct(ObjectRepository $or, ObjectManager $om, DbAbstractService $service)
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

    protected $config = [];

    public function add($type, $value, $append = false)
    {
        if (!$append || !isset($this->config[$type])) {
            $this->config[$type] = [];
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
            $whereId = [$whereId];
        }

        return $this->add('joinWhere', [$table, $whereId, $subSelectField], true);
    }

    public function addWhere($field, $transformer, $value = null, $forceType = null)
    {
        return $this->add('where', [$field, $transformer, $value, $forceType], true);
    }

    public function orderBy($sort, $order = null)
    {
        return $this->add('orderBy', [$sort, $order]);
    }

    public function addOrderBy($sort, $order = null)
    {
        return $this->add('orderBy', [$sort, $order], true);
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
