<?php

namespace Sys25\RnBase\Database;

use Countable;
use Iterator;
use ReturnTypeWillChange;
use Sys25\RnBase\Utility\TYPO3;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ResultIterator implements Iterator, Countable
{
    private $queryBuilderFactory;
    private $from;
    private $arr;
    private $queryBuilder;
    private $result;
    private $currentRow;
    private $key = 0;
    private $rowWrapper;

    public function __construct(callable $queryBuilderFactory, $from, $arr, callable $rowWrapper)
    {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->from = $from;
        $this->arr = $arr;
        $this->rowWrapper = $rowWrapper;
        $this->rewind();
    }

    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->key = 0;
        $this->queryBuilder = call_user_func($this->queryBuilderFactory, $this->from, $this->arr);
        $executeMethod = method_exists($this->queryBuilder, 'executeQuery') ? 'executeQuery' : 'execute';
        $this->result = $this->queryBuilder->$executeMethod();
        $this->fetchNextRow();
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->currentRow;
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

    #[ReturnTypeWillChange]
    public function next()
    {
        $this->fetchNextRow();
        ++$this->key;
    }

    #[ReturnTypeWillChange]
    public function valid()
    {
        return false !== $this->currentRow && null !== $this->currentRow;
    }

    private function fetchNextRow()
    {
        if (TYPO3::isTYPO130OrHigher()) {
            $row = $this->result->fetchAssociative();
        } else {
            $row = $this->result->fetch();
        }
        if (false !== $row && null !== $row && $this->rowWrapper) {
            $row = call_user_func($this->rowWrapper, $row);
        }
        $this->currentRow = $row;
    }

    #[ReturnTypeWillChange]
    public function count()
    {
        // Die Implementierung funktioniert vermutlich nicht sicher. Der Aufruf ist im
        // Normalfall auch nicht nötig. Der Iterator soll nur das ResultSet durchlaufen.
        $arr = $this->arr;
        $arr['what'] = 'COUNT(*) as cnt';
        // Limit und Offset entfernen, damit alle Zeilen gezählt werden
        unset($arr['limit'], $arr['offset'], $arr['orderby'], $arr['groupby'], $arr['having']);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = call_user_func($this->queryBuilderFactory, $this->from, $arr);
        $executeMethod = method_exists($queryBuilder, 'executeQuery') ? 'executeQuery' : 'execute';
        $result = $queryBuilder->$executeMethod();

        if (TYPO3::isTYPO130OrHigher()) {
            $row = $result->fetchAssociative();

            return (int) array_shift($row);
        } else {
            $row = $result->fetch();

            return (int) $row['cnt'];
        }
    }
}
