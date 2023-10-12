<?php

namespace NyroDev\UtilityBundle\QueryBuilder;

use DateTime;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Range;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\ResultSet;
use Exception;

class ElasticaQueryBuilder extends AbstractQueryBuilder
{
    protected $indexName;
    protected $indexType;
    protected $scoreSortAdded = false;

    public function setIndexName($indexName)
    {
        $this->indexName = $indexName;
    }

    public function getIndexName()
    {
        return $this->indexName;
    }

    protected function checkIndexName()
    {
        if (!$this->indexName) {
            throw new Exception('No index name provided for ElasticaQueryBuilder.');
        }
    }

    public function setIndexType($indexType)
    {
        $this->indexType = $indexType;
    }

    public function getIndexType()
    {
        if (!$this->indexType) {
            $tmp = explode('\\', $this->or->getClassName());
            $this->indexType = strtolower($tmp[count($tmp) - 1]);
        }

        return $this->indexType;
    }

    public function useScoreSort()
    {
        $this->scoreSortAdded = true;
    }

    protected function _count()
    {
        $this->checkIndexName();

        $query = $this->getNewQueryBuilder(true);
        $query->setSize(0);

        $results = $this->service->get('fos_elastica.index.'.$this->getIndexName().'.'.$this->getIndexType())->search($query);
        /* @var $results ResultSet */

        return $results->getTotalHits();
    }

    public function getResult()
    {
        $this->checkIndexName();

        $query = $this->getNewQueryBuilder();

        return $this->service->get('fos_elastica.finder.'.$this->getIndexName().'.'.$this->getIndexType())->find($query);
    }

    public function getNewQueryBuilder($complete = false)
    {
        $boolQuery = new BoolQuery();
        if (isset($this->config['where'])) {
            $this->applyFilterArr($boolQuery, $this->config['where']);
        }

        if (isset($this->config['joinWhere']) && count($this->config['joinWhere'])) {
            foreach ($this->config['joinWhere'] as $where) {
                list($name, $values, $subSelectField) = $where;
                if ('id' === $subSelectField) {
                    $boolQuery->addFilter(new Terms($name, $values));
                } else {
                    throw new Exception('joinWhere for ElasticaQueryBuilder supports only linearized id fields .');
                }
            }
        }

        $queryBuilder = new Query($boolQuery);

        if ($this->scoreSortAdded) {
            $queryBuilder->addSort(['_score' => 'desc']);
        }

        if (isset($this->config['orderBy'])) {
            foreach ($this->config['orderBy'] as $orderBy) {
                list($sort, $dir) = $orderBy;
                $queryBuilder->addSort([$sort => $dir]);
            }
        }

        if (!$complete) {
            if (isset($this->config['firstResult'])) {
                $queryBuilder->setFrom($this->config['firstResult']);
            }
            if (isset($this->config['maxResults'])) {
                $queryBuilder->setSize($this->config['maxResults']);
            }
        }

        return $queryBuilder;
    }

    protected function applyFilterArr(BoolQuery $query, array $whereArr)
    {
        $nbWhere = 0;
        foreach ($whereArr as $where) {
            list($field, $transformer, $value, $forceType) = array_merge($where, array_fill(0, 4, false));

            if (self::WHERE_OR === $field) {
                $subQuery = new BoolQuery();
                $nbOr = 0;
                foreach ($transformer as $whereOr) {
                    $fieldOr = $whereOr[0];
                    $transformerOr = $whereOr[1];
                    if (self::WHERE_SUB === $fieldOr) {
                        $subQuery2 = new BoolQuery();
                        if ($this->applyFilterArr($subQuery2, $transformerOr)) {
                            $subQuery->addShould($subQuery2);
                            ++$nbOr;
                        }
                    } else {
                        $valueOr = isset($whereOr[2]) ? $whereOr[2] : null;
                        $forceTypeOr = isset($whereOr[3]) ? $whereOr[3] : null;

                        $subQuery2 = new BoolQuery();
                        if ($this->applyFilter($subQuery2, $fieldOr, $transformerOr, $valueOr)) {
                            $subQuery->addShould($subQuery2);
                            ++$nbOr;
                        }
                    }
                }
                if ($nbOr) {
                    $query->addMust($subQuery);
                    ++$nbWhere;
                }
            } else {
                if ($this->applyFilter($query, $field, $transformer, $value)) {
                    ++$nbWhere;
                }
            }
        }

        return $nbWhere;
    }

    protected function applyFilter(BoolQuery $query, $field, $transformer, $value)
    {
        switch ($transformer) {
            case self::OPERATOR_EQUALS:
                $query->addFilter(new Term([$field => $value]));
                break;
            case self::OPERATOR_NOT_EQUALS:
                $query->addMustNot(new Term([$field => $value]));
                break;
            case self::OPERATOR_GT:
                $query->addFilter(new Range($field, ['gt' => $value]));
                break;
            case self::OPERATOR_GTE:
                $query->addFilter(new Range($field, ['gte' => $value]));
                break;
            case self::OPERATOR_LT:
                $query->addFilter(new Range($field, ['lt' => $value]));
                break;
            case self::OPERATOR_LTE:
                $query->addFilter(new Range($field, ['lte' => $value]));
                break;
            case self::OPERATOR_LIKE:
            case self::OPERATOR_CONTAINS:
                $qs = new SimpleQueryString($value);
                $qs->setFields(array_unique(is_array($field) ? $field : explode(',', $field)));
                $qs->setDefaultOperator('AND');
                $query->addMust($qs);
                break;
            case self::OPERATOR_LIKEDATE:
                $dateStart = new DateTime($value);
                $dateStart->setTime(0, 0, 0);
                $dateEnd = new DateTime($value);
                $dateEnd->setTime(23, 59, 59);
                $query->addFilter(new Range($field, [
                    'gte' => $dateStart->getTimestamp(),
                    'lte' => $dateEnd->getTimestamp(),
                ]));
                break;
            case self::OPERATOR_IN:
                $query->addFilter(new Terms($field, $value));
                break;
            case self::OPERATOR_NOT_IN:
                $query->addMustNot(new Terms($field, $value));
                break;
            case self::OPERATOR_IS_NULL:
                $query->addMustNot(new Exists($field));
                break;
            case self::OPERATOR_IS_NOT_NULL:
                $query->addFilter(new Exists($field));
                break;
            default:
                return false;
        }

        return true;
    }
}
