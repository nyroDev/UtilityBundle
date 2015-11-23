<?php

namespace NyroDev\UtilityBundle\QueryBuilder;

class OrmQueryBuilder extends AbstractQueryBuilder {
	
	protected function _buildRealQueryBuilder() {
		$alias = 'l';
		$this->queryBuilder = $this->or->createQueryBuilder($alias);
		
		$prmNb = 0;
		if (isset($this->config['where'])) {
			foreach($this->config['where'] as $where) {
				list($field, $transformer, $value, $forceType) = $where;
				
				if ($transformer == self::WHERE_IS_NOT_NULL || $transformer == self::WHERE_IS_NULL) {
					$this->queryBuilder->andWhere($alias.'.'.$field.' '.$transformer);
				} else {
					$prm = 'param_'.$prmNb;
					$this->queryBuilder
							->andWhere($alias.'.'.$field.' '.$transformer.' :'.$prm)
							->setParameter($prm, $value, $forceType);
					$prmNb++;
				}
			}
		}
		
		if (isset($this->config['joinWhere'])) {
			foreach($this->config['joinWhere'] as $where) {
				list($name, $values) = $where;
				$prm = 'param_'.$prmNb;
				$this->queryBuilder
					->join($alias.'.'.$name, $name)
					->andWhere($name.'.id IN (:'.$prm.')')
					->setParameter($prm, $values);
					$prmNb++;
			}
		}
		
		if (isset($this->config['orderBy'])) {
			foreach($this->config['orderBy'] as $orderBy) {
				list($sort, $dir) = $orderBy;
				$this->queryBuilder->addOrderBy($alias.'.'.$sort, $dir);
			}
		}
		
		$this->count = $this->_count($this->queryBuilder);
		
		if (isset($this->config['firstResult']))
			$this->queryBuilder->setFirstResult($this->config['firstResult']);
		if (isset($this->config['maxResults']))
			$this->queryBuilder->setMaxResults($this->config['maxResults']);
	}
	
	protected function _count($queryBuilder) {
		return $this->or
			->createQueryBuilder('cpt')
				->select('COUNT(cpt.id)')
				->andWhere('cpt.id = ANY('.$queryBuilder->getDQL().')')
				->setParameters($queryBuilder->getParameters())
				->getQuery()->getSingleScalarResult();
	}

}