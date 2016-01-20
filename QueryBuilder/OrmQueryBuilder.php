<?php

namespace NyroDev\UtilityBundle\QueryBuilder;

class OrmQueryBuilder extends AbstractQueryBuilder {
	
	protected function _buildRealQueryBuilder() {
		return $this->getNewQueryBuilder();
	}
	
	public function getNewQueryBuilder($complete = false) {
		$alias = 'l';
		$queryBuilder = $this->or->createQueryBuilder($alias);
		
		$this->prmNb = 0;
		if (isset($this->config['where']))
			$this->applyFilterArr($queryBuilder, $alias, $this->config['where']);
		
		if (isset($this->config['joinWhere'])) {
			foreach($this->config['joinWhere'] as $where) {
				list($name, $values, $subSelectField) = $where;
				$prm = 'param_'.$this->prmNb;
				$queryBuilder
					->join($alias.'.'.$name, $name)
					->andWhere($name.'.'.$subSelectField.' IN (:'.$prm.')')
					->setParameter($prm, $values);
				$this->prmNb++;
			}
		}
		
		if (isset($this->config['orderBy'])) {
			foreach($this->config['orderBy'] as $orderBy) {
				list($sort, $dir) = $orderBy;
				$queryBuilder->addOrderBy($alias.'.'.$sort, $dir);
			}
		}
		
		if (!$complete) {
			if (isset($this->config['firstResult']))
				$queryBuilder->setFirstResult($this->config['firstResult']);
			if (isset($this->config['maxResults']))
				$queryBuilder->setMaxResults($this->config['maxResults']);
		}
		
		return $queryBuilder;
	}
	
	public function getResult() {
		return $this->getQuery()
				->getResult();
	}
	
	protected function _count() {
		$queryBuilder = $this->getNewQueryBuilder(true);
		return $this->or
			->createQueryBuilder('cpt')
				->select('COUNT(cpt.id)')
				->andWhere('cpt.id = ANY('.$queryBuilder->getDQL().')')
				->setParameters($queryBuilder->getParameters())
				->getQuery()->getSingleScalarResult();
	}

	protected function applyFilterArr($object, $alias, array $whereArr, $queryBuilder) {
		$nbWhere = 0;
		foreach($whereArr as $where) {
			list($field, $transformer, $value, $forceType) = array_merge($where, array_fill(0, 4, false));

			if ($field === self::WHERE_OR) {
				$exprOr = $queryBuilder->expr();
				$nbOr = 0;
				
				foreach($transformer as $whereOr) {
					$fieldOr = $whereOr[0];
					$transformerOr = $whereOr[1];
					if ($fieldOr === self::WHERE_SUB) {
						$expr = $queryBuilder->expr();
						if ($this->applyFilterArr($expr, $alias, $transformerOr, $queryBuilder)) {
							$exprOr->orWhere($expr);
							$nbOr++;
						}
					} else {
						$valueOr = isset($whereOr[2]) ? $whereOr[2] : null;
						$forceTypeOr = isset($whereOr[3]) ? $whereOr[3] : null;

						$expr = $queryBuilder->expr();
						if ($this->applyFilter($expr, $alias, $fieldOr, $transformerOr, $valueOr, $forceTypeOr, $queryBuilder)) {
							$exprOr->orWhere($expr);
							$nbOr++;
						}
					}
				}
				
				if ($nbOr) {
					$object->andWhere($exprOr);
					$nbWhere++;
				}
			} else {
				if ($this->applyFilter($object, $alias, $field, $transformer, $value, $forceType, $queryBuilder))
					$nbWhere++;
			}
		}
		$nbWhere++;
	}
	
	protected function applyFilter($object, $alias, $field, $transformer, $value, $forceType, $queryBuilder) {
		switch($transformer) {
			case self::OPERATOR_IS_NULL:
			case self::OPERATOR_IS_NOT_NULL:
				$object->andWhere($alias.'.'.$field.' '.$transformer);
				break;
			case self::OPERATOR_CONTAINS:
				$prm = 'param_'.$this->prmNb;
				$object->andWhere($alias.'.'.$field.' LIKE :'.$prm);
				$queryBuilder->setParameter($prm, '%'.$value.'%', $forceType);
				$this->prmNb++;
				break;
			case self::OPERATOR_LIKEDATE:
				$prm = 'param_'.$this->prmNb;
				$object->andWhere($alias.'.'.$field.' LIKE :'.$prm);
				$queryBuilder->setParameter($prm, $value.'%', $forceType);
				$this->prmNb++;
				break;
			default:
				$prm = 'param_'.$this->prmNb;
				$needParenthesis = $transformer === self::OPERATOR_IN;
				if ($transformer === self::OPERATOR_CONTAINS) {
					$transformer = 'LIKE';
					$value = '%'.$value.'%';
				} else if ($transformer === self::OPERATOR_LIKEDATE) {
					$transformer = 'LIKE';
					$value = $value.'%';
				}
				$object->andWhere($alias.'.'.$field.' '.$transformer.' '.($needParenthesis ? '(' : '').':'.$prm.($needParenthesis ? ')' : ''));
				$queryBuilder->setParameter($prm, $value.'%', $forceType);
				$this->prmNb++;
				break;
		}
	}

}