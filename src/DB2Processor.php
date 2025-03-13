<?php

namespace BWICompanies\DB2Driver;

use BWICompanies\DB2Driver\DB2QueryGrammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;

class DB2Processor extends Processor
{
    /**
     * Process an  "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder   $query
     * @param  string                               $sql
     * @param  array                                $values
     * @param  string|null                          $sequence
     * 
     * @return int|array
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $sequenceStr = $sequence ?: 'id';

        if (is_array($sequence)) {
            $grammar = new DB2QueryGrammar;
            $sequenceStr = $grammar->columnize($sequence);
        }

        $sqlStr = 'select %s from new table (%s)';

        $finalSql = sprintf($sqlStr, $sequenceStr, $sql);
        $results = $query->getConnection()
                         ->select($finalSql, $values);

        if (is_array($sequence)) {
            return array_values((array) $results[0]);
        } else {
            $result = (array) $results[0];
            if (isset($result[$sequenceStr])) {
                $id = $result[$sequenceStr];
            } else {
                $id = $result[strtoupper($sequenceStr)];
            }

            return is_numeric($id) ? (int) $id : $id;
        }
    }

    /**
     * Process the results of a column listing query.
     * This was present in Illuminate\Database\Query\Processor.php 9.x but later removed.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return $results;
    }
}