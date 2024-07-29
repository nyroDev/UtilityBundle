<?php

namespace NyroDev\UtilityBundle\QueryBuilder;

class OrmQueryBuilder extends AbstractQueryBuilder
{

    private $prmNb = 0;

    public function getNewQueryBuilder($complete = false)
    {
        $alias = 'l';
        $queryBuilder = $this->or->createQueryBuilder($alias);

        $this->prmNb = 0;
        if (isset($this->config['where'])) {
            $filters = $this->applyFilterArr($alias, $this->config['where'], $queryBuilder);
            foreach ($filters as $f) {
                $queryBuilder->andWhere($f);
            }
        }

        if (isset($this->config['join'])) {
            foreach ($this->config['join'] as $join) {
                list($table, $aliasJoin) = $join;
                $queryBuilder->join($this->useAlias($alias, $table).$table, $aliasJoin);
            }
        }

        if (isset($this->config['joinWhere'])) {
            foreach ($this->config['joinWhere'] as $where) {
                list($name, $values, $subSelectField) = $where;
                $prm = 'param_'.$this->prmNb;
                $queryBuilder
                    ->join($alias.'.'.$name, $name)
                    ->andWhere($name.'.'.$subSelectField.' IN (:'.$prm.')')
                    ->setParameter($prm, $values);
                ++$this->prmNb;
            }
        }

        if (isset($this->config['orderBy'])) {
            foreach ($this->config['orderBy'] as $orderBy) {
                list($sort, $dir) = $orderBy;
                $queryBuilder->addOrderBy($this->useAlias($alias, $sort).$sort, $dir);
            }
        }

        if (!$complete) {
            if (isset($this->config['firstResult'])) {
                $queryBuilder->setFirstResult($this->config['firstResult']);
            }
            if (isset($this->config['maxResults'])) {
                $queryBuilder->setMaxResults($this->config['maxResults']);
            }
        }

        return $queryBuilder;
    }

    public function getResult()
    {
        return $this->getQuery()
                ->getResult();
    }

    protected function _count()
    {
        $queryBuilder = $this->getNewQueryBuilder(true);

        $fieldIdentifier = 'id';
        if (method_exists($this->or, 'getFieldIdentifier')) {
            $fieldIdentifier = $this->or->getFieldIdentifier();
        }

        return $this->or
            ->createQueryBuilder('cpt')
                ->select('COUNT(cpt.'.$fieldIdentifier.')')
                ->andWhere('cpt.'.$fieldIdentifier.' = ANY('.$queryBuilder->getDQL().')')
                ->setParameters($queryBuilder->getParameters())
                ->getQuery()
                ->getSingleScalarResult();
    }

    protected function applyFilterArr($alias, array $whereArr, $queryBuilder)
    {
        $filters = [];
        foreach ($whereArr as $where) {
            list($field, $transformer, $value, $forceType) = array_merge($where, array_fill(0, 4, false));

            if (self::WHERE_OR === $field) {
                $tmpOr = [];

                foreach ($transformer as $whereOr) {
                    $fieldOr = $whereOr[0];
                    $transformerOr = $whereOr[1];
                    if (self::WHERE_SUB === $fieldOr) {
                        $tmpSub = $this->applyFilterArr($alias, $transformerOr, $queryBuilder);
                        if (count($tmpSub)) {
                            $tmpOr[] = implode(' AND ', $tmpSub);
                        }
                    } else {
                        $valueOr = isset($whereOr[2]) ? $whereOr[2] : null;
                        $forceTypeOr = isset($whereOr[3]) ? $whereOr[3] : null;

                        $tmpOr[] = $this->applyFilter($alias, $fieldOr, $transformerOr, $valueOr, $forceTypeOr, $queryBuilder);
                    }
                }
                $tmpOr = array_filter($tmpOr);
                if (count($tmpOr)) {
                    $filters[] = implode(' OR ', $tmpOr);
                }
            } else {
                $filters[] = $this->applyFilter($alias, $field, $transformer, $value, $forceType, $queryBuilder);
            }
        }

        return array_filter($filters);
    }

    protected function applyFilter($alias, $field, $transformer, $value, $forceType, $queryBuilder)
    {
        $ret = null;
        $useAlias = $this->useAlias($alias, $field);
        switch ($transformer) {
            case self::OPERATOR_IS_NULL:
            case self::OPERATOR_IS_NOT_NULL:
                $ret = $useAlias.$field.' '.$transformer;
                break;
            case self::OPERATOR_CONTAINS:
                $prm = 'param_'.$this->prmNb;
                $ret = $useAlias.$field.' LIKE :'.$prm;
                $queryBuilder->setParameter($prm, '%'.$value.'%', $forceType);
                ++$this->prmNb;
                break;
            case self::OPERATOR_LIKEDATE:
                $prm = 'param_'.$this->prmNb;
                $ret = $useAlias.$field.' LIKE :'.$prm;
                $queryBuilder->setParameter($prm, $value.'%', $forceType);
                ++$this->prmNb;
                break;
            default:
                $prm = 'param_'.$this->prmNb;
                $needParenthesis = self::OPERATOR_IN === $transformer || self::OPERATOR_NOT_IN === $transformer;
                if (self::OPERATOR_CONTAINS === $transformer) {
                    $transformer = 'LIKE';
                    $value = '%'.$value.'%';
                }
                /* @var $object \Doctrine\ORM\Query\Expr */
                $ret = $useAlias.$field.' '.$transformer.' '.($needParenthesis ? '(' : '').':'.$prm.($needParenthesis ? ')' : '');
                $queryBuilder->setParameter($prm, $value, $forceType);
                ++$this->prmNb;
                break;
        }

        return $ret;
    }

    protected function useAlias($alias, $field) {
        return strpos($field, '.') === false ? $alias.'.' : null;
    }
}
