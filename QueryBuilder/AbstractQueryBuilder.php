<?php
namespace NyroDev\UtilityBundle\QueryBuilder;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\Services\MainService;
use Symfony\Component\Serializer\Exception\RuntimeException;

/**
 * Pager utility object 
 */
abstract class AbstractQueryBuilder {

	const WHERE_IS_NULL = 'IS NULL';
	const WHERE_IS_NOT_NULL = 'IS NOT NULL';
	
	/**
	 * nyroDev service object used to generate the URL
	 *
	 * @var MainService
	 */
	protected $service;
	
	/**
	 *
	 * @var ObjectRepository
	 */
	protected $or;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $om;
	
	public function __construct(ObjectRepository $or, ObjectManager $om) {
		$this->or = $or;
		$this->om = $om;
	}
	
	protected $config = array();
	
	public function add($type, $value, $append = false) {
		if (!$append || !isset($this->config[$type])) {
			$this->config[$type] = array();
		}
		$this->config[$type][] = $value;
		
		return $this;
	}
	
	public function addJoinWhere($table, array $whereId) {
		return $this->add('joinWhere', array($table, $whereId), true);
	}
	
	public function addWhere($field, $transformer, $value = null, $forceType = null) {
		return $this->add('where', array($field, $transformer, $value, $forceType), true);
	}
	
	public function orderBy($sort, $order = null) {
		return $this->add('orderBy', array($sort, $order));
	}
	
	public function addOrderBy($sort, $order = null) {
		return $this->add('orderBy', array($sort, $order), true);
	}
	
	public function setFirstResult($firstResult) {
		$this->config['firstResult'] = $firstResult;
		return $this;
	}
	
	public function setMaxResults($maxResults) {
		$this->config['maxResults'] = $maxResults;
		return $this;
	}
	
	protected $built;
	protected $queryBuilder;
	protected $count;
	protected function buildRealQueryBuilder() {
		if ($this->built)
			throw new RuntimeException('NyroDev\UtilityBundle\Utility\QueryBuilder can be built only once.');
		
		$this->_buildRealQueryBuilder();
		
		if (is_null($this->queryBuilder))
			throw new RuntimeException(get_class($this).' did not built query builder correctly.');
		
		if (is_null($this->count))
			throw new RuntimeException(get_class($this).' did not fill count correctly.');
		
		$this->built = true;
	}
	
	abstract protected function _buildRealQueryBuilder();
	
	public function getQueryBuilder() {
		if (!$this->built)
			$this->buildRealQueryBuilder();
		return $this->queryBuilder;
	}
	
	public function getQuery() {
		return $this->getQueryBuilder()->getQuery();
	}
	
	public function count() {
		return $this->count;
	}
	
}