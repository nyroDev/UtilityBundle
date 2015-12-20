<?php

namespace NyroDev\UtilityBundle\QueryBuilder;

class MongodbQueryBuilder extends AbstractQueryBuilder {
	
	protected function _buildRealQueryBuilder() {
		return $this->getNewQueryBuilder();
	}
	
	public function getNewQueryBuilder($complete = false) {
		$queryBuilder = $this->or->createQueryBuilder();
		
		if (isset($this->config['where'])) {
			foreach($this->config['where'] as $where) {
				list($field, $transformer, $value, $forceType) = $where;
				
				if ($field === self::WHERE_OR) {
					$exprOr = $queryBuilder->expr();
					$nbOr = 0;
					foreach($transformer as $whereOr) {
						$fieldOr = $whereOr[0];
						$transformerOr = $whereOr[1];
						$valueOr = isset($whereOr[2]) ? $whereOr[2] : null;
						$forceTypeOr = isset($whereOr[3]) ? $whereOr[3] : null;

						if ($this->applyFilter($exprOr, $fieldOr, $transformerOr, $valueOr, $queryBuilder))
							$nbOr++;
					}
					if ($nbOr)
						$queryBuilder->addAnd($exprOr);
				} else {
					$this->applyFilter($queryBuilder, $field, $transformer, $value, $queryBuilder);
				}
			}
		}
		
		if (isset($this->config['joinWhere'])) {
			foreach($this->config['joinWhere'] as $where) {
				list($name, $values, $subSelectField) = $where;
				// @todo test it
				if ($subSelectField == 'id') {
					$queryBuilder->field($name)->in($values);
				} else {
					$founds = array();
					$tmp = $this->service->getRepository($name)->findBy(array($subSelectField=>$values));
					foreach($tmp as $t)
						$founds[] = $t->getId();
					if (count($founds)) {
						$queryBuilder->field($name)->in($founds);
					} else {
						$queryBuilder->field($name)->equals(0);
					}
				}
			}
		}
		
		if (isset($this->config['orderBy'])) {
			foreach($this->config['orderBy'] as $orderBy) {
				list($sort, $dir) = $orderBy;
				$queryBuilder->sort($sort, $dir);
			}
		}
		
		if (!$complete) {
			if (isset($this->config['firstResult']))
				$queryBuilder->skip($this->config['firstResult']);
			if (isset($this->config['maxResults']))
				$queryBuilder->limit($this->config['maxResults']);
		}
		
		return $queryBuilder;
	}
	
	protected function applyFilter($object, $field, $transformer, $value, $queryBuilder) {
		switch($transformer) {
			case self::OPERATOR_EQUALS:
				$object->field($field)->equals($value);
				break;
			case self::OPERATOR_GT:
				$object->field($field)->gt($value);
				break;
			case self::OPERATOR_GTE:
				$object->field($field)->gte($value);
				break;
			case self::OPERATOR_LT:
				$object->field($field)->lt($value);
				break;
			case self::OPERATOR_LTE:
				$object->field($field)->lte($value);
				break;
			case self::OPERATOR_LIKE:
				$object->field($field)->gt(new \MongoRegex('/'.preg_quote($value, '/').'/i'));
				break;
			case self::OPERATOR_CONTAINS:
				$object->field($field)->gt(new \MongoRegex('/.*'.preg_quote($value, '/').'.*/i'));
				break;
			case self::OPERATOR_LIKEDATE:
				$dateStart = new \DateTime($value);
				$dateStart->setTime(0, 0, 0);
				$dateEnd = new \DateTime($value);
				$dateEnd->setTime(23, 59, 59);
				$object
					->field($field)->gte($dateStart)
					->field($field)->lte($dateEnd);
				break;
			case self::OPERATOR_IN:
				$object->field($field)->in($value);
				break;
			case self::OPERATOR_IS_NULL:
				$expr = $queryBuilder->expr();
				$expr->addOr($queryBuilder->expr()->field($field)->exists(false));
				$expr->addOr($queryBuilder->expr()->field($field)->equals(null));
				$expr->addOr($queryBuilder->expr()->field($field)->equals(''));
				$object->addAnd($expr);
				break;
			case self::OPERATOR_IS_NOT_NULL:
				$expr = $queryBuilder->expr();
				$expr->addAnd($queryBuilder->expr()->field($field)->exists(true));
				$expr->addAnd($queryBuilder->expr()->field($field)->notEqual(null));
				$expr->addAnd($queryBuilder->expr()->field($field)->notEqual(''));
				$object->addAnd($expr);
				break;
			default:
				return false;
		}
		return true;
	}
	
	protected function _count() {
		return $this->getNewQueryBuilder(true)
				->count()
				->getQuery()
				->count();
	}

}