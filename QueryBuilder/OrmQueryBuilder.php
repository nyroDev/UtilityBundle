<?php

namespace NyroDev\UtilityBundle\QueryBuilder;

class OrmQueryBuilder extends AbstractQueryBuilder {
	
	protected function _buildRealQueryBuilder() {
		return $this->getNewQueryBuilder();
	}
	
	public function getNewQueryBuilder($complete = false) {
		$alias = 'l';
		$queryBuilder = $this->or->createQueryBuilder($alias);
		
		$prmNb = 0;
		if (isset($this->config['where'])) {
			foreach($this->config['where'] as $where) {
				list($field, $transformer, $value, $forceType) = $where;
				
				if ($field === self::WHERE_OR) {
					$tmp = array();
					foreach($transformer as $whereOr) {
						$fieldOr = $whereOr[0];
						$transformerOr = $whereOr[1];
						$valueOr = isset($whereOr[2]) ? $whereOr[2] : null;
						$forceTypeOr = isset($whereOr[3]) ? $whereOr[3] : null;
						if ($transformerOr === self::OPERATOR_IS_NOT_NULL || $transformerOr === self::OPERATOR_IS_NULL) {
							$tmp[] = $alias.'.'.$fieldOr.' '.$transformerOr;
						} else {
							$prm = 'param_'.$prmNb;
							$needParenthesis = $transformerOr === self::OPERATOR_IN;
							if ($transformerOr === self::OPERATOR_CONTAINS) {
								$transformerOr = 'LIKE';
								$valueOr = '%'.$valueOr.'%';
							} else if ($transformerOr === self::OPERATOR_LIKEDATE) {
								$transformerOr = 'LIKE';
								$valueOr = $valueOr.'%';
							}
							$tmp[] = $alias.'.'.$fieldOr.' '.$transformerOr.' '.($needParenthesis ? '(' : '').':'.$prm.($needParenthesis ? ')' : '');
							$queryBuilder->setParameter($prm, $valueOr, $forceTypeOr);
							$prmNb++;
						}
					}
					if (count($tmp))
						$queryBuilder->andWhere(implode(' OR ', $tmp));
				} else {
					if ($transformer === self::OPERATOR_IS_NOT_NULL || $transformer === self::OPERATOR_IS_NULL) {
						$queryBuilder->andWhere($alias.'.'.$field.' '.$transformer);
					} else {
						$prm = 'param_'.$prmNb;
						$needParenthesis = $transformer === self::OPERATOR_IN;
						if ($transformer === self::OPERATOR_CONTAINS) {
							$transformer = 'LIKE';
							$value = '%'.$value.'%';
						} else if ($transformer === self::OPERATOR_LIKEDATE) {
							$transformer = 'LIKE';
							$value = $value.'%';
						}
						$queryBuilder
								->andWhere($alias.'.'.$field.' '.$transformer.' '.($needParenthesis ? '(' : '').':'.$prm.($needParenthesis ? ')' : ''))
								->setParameter($prm, $value, $forceType);
						$prmNb++;
					}
				}
			}
		}
		
		if (isset($this->config['joinWhere'])) {
			foreach($this->config['joinWhere'] as $where) {
				list($name, $values, $subSelectField) = $where;
				$prm = 'param_'.$prmNb;
				$queryBuilder
					->join($alias.'.'.$name, $name)
					->andWhere($name.'.'.$subSelectField.' IN (:'.$prm.')')
					->setParameter($prm, $values);
				$prmNb++;
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
	
	protected function _count() {
		$queryBuilder = $this->getNewQueryBuilder(true);
		return $this->or
			->createQueryBuilder('cpt')
				->select('COUNT(cpt.id)')
				->andWhere('cpt.id = ANY('.$queryBuilder->getDQL().')')
				->setParameters($queryBuilder->getParameters())
				->getQuery()->getSingleScalarResult();
	}

}