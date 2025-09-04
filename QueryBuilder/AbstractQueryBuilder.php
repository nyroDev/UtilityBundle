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

    protected array $config = [];

    protected bool $built = false;
    protected mixed $queryBuilder = null;
    protected ?int $count = null;

    public function __construct(
        protected readonly ObjectRepository $or,
        protected readonly ObjectManager $om,
        protected readonly DbAbstractService $service,
    ) {
    }

    public function __clone()
    {
        $this->built = false;
        $this->queryBuilder = null;
        $this->count = null;
    }

    public function add(string $type, mixed $value, bool $append = false): self
    {
        if (!$append || !isset($this->config[$type])) {
            $this->config[$type] = [];
        }
        $this->config[$type][] = $value;

        return $this;
    }

    public function get(string $type): mixed
    {
        return isset($this->config[$type]) ? $this->config[$type] : null;
    }

    public function addJoin(string $table, string $alias, bool $leftJoin = false): self
    {
        return $this->add('join', [$table, $alias, $leftJoin], true);
    }

    public function addJoinWhere(string $table, string|array $whereId, string $subSelectField = 'id'): self
    {
        if (!is_array($whereId)) {
            $whereId = [$whereId];
        }

        return $this->add('joinWhere', [$table, $whereId, $subSelectField], true);
    }

    public function addWhere(string $field, array|string $transformer, mixed $value = null, mixed $forceType = null): self
    {
        return $this->add('where', [$field, $transformer, $value, $forceType], true);
    }

    public function orderBy(string $sort, ?string $order = null): self
    {
        return $this->add('orderBy', [$sort, $order]);
    }

    public function addOrderBy(string $sort, ?string $order = null): self
    {
        return $this->add('orderBy', [$sort, $order], true);
    }

    public function setFirstResult(int $firstResult): self
    {
        $this->config['firstResult'] = $firstResult;

        return $this;
    }

    public function setMaxResults(int $maxResults): self
    {
        $this->config['maxResults'] = $maxResults;

        return $this;
    }

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
     * @return Specific query builder
     */
    abstract public function getNewQueryBuilder(bool $complete = false);

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

    public function count(): int
    {
        if (is_null($this->count)) {
            $this->count = $this->_count();
        }

        return $this->count;
    }

    abstract protected function _count(): int;
}
