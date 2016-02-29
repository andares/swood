<?php

/*
 * Copyright (C) 2016 andares.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Redb\Model;
use Swood\Debug as D;

/**
 * Description of Solr
 *
 * @author andares
 */
abstract class Solr extends Model {

    /**
     * 不同DB类型的Model扩展方法
     */
    protected static function _read($id, \Redb\Driver\Driver $conn) {
        /* @var $conn \Redb\Driver\Solr */

    }

    protected static function _create($id, array $data, \Redb\Driver\Driver $conn) {
        /* @var $conn \Redb\Driver\Solr */

    }

    protected static function _update($id, array $data, \Redb\Driver\Driver $conn) {
        /* @var $conn \Redb\Driver\Solr */

    }

    protected static function _delete($id, \Redb\Driver\Driver $conn) {
        /* @var $conn \Redb\Driver\Solr */

    }

    protected static function _query(\Redb\Query $query, \Redb\Driver\Driver $conn) {
        /* @var $conn \Redb\Driver\Solr */
        // 字段
        $fields = $query->selectFields();
        $fields && $fields = urlencode(implode(' ', $fields));

        // 排序
        $sort = $query->getSort();
        $sort && $sort = urlencode(static::_buildQuerySort($sort));

        // 调用
        $response = $conn->query(static::_buildQuery($query), $fields, $sort,
            $query->getLimit(), $query->getSkip())
            ->post(static::$_name);

        if (!$response) {
            return $response;
        }

        $result = new \Redb\Result($response['response']['docs'], [
            'start'      => $response['response']['start'],
            'status'     => $response['responseHeader']['status'],
            'timecost'   => $response['responseHeader']['QTime'],
        ]);
        $result->total_rows = $response['response']['numFound'];
        return $result;
    }

    /**
     * 创建排序
     * @param \Redb\Query $query
     */
    private static function _buildQuery(\Redb\Query $query) {
        $q  = [];
        $fq = [];
        $qf = '';
        foreach ($query->getMixed() as $group => $conds) {
            switch ($group) {
                case 'q':       // keywords q=*:* q=上海
                    foreach ($conds as $cond) {
                        $q[] = static::_buildQuery_q($cond);
                    }
                    break;

                case 'fq':      // 筛选条件, 可被缓存 fq=type:(3 4)&fq=level:3&fq=level:[2 TO *]
                    foreach ($conds as $cond) {
                        $fq[] = 'fq=' . urlencode(static::_buildQuery_fq($cond));
                    }
                    break;
                case 'qf':      // 查询字段 qf=names_cjk name
                    $qf = is_array($conds) ? implode(' ', $conds) : $conds;
                    break;
                case 'df':      // 默认查询字段 暂不支持
                    break;
                case 'defType': // defType=edismax 暂不支持
                    break;

                default:
                    break;
            }
        }

        $result = [];
        if ($q) {
            $result['q'] = urlencode(implode(' ', $q));
        } else {
            $result['q'] = urlencode('*:*');
        }
        $fq && $result['fq']    = implode('&', $fq);
        $qf && $result['qf']    = urlencode($qf);
        return $result;
    }

    /**
     * 构建查询 q
     * @param array $cond
     * @return string
     */
    private static function _buildQuery_q(array $cond) {
        if ($cond[1] == '%') {
            return $cond[2];
        }

        return "$cond[0]:$cond[2]";
    }


    /**
     * 构建查询 fq
     * @param array $cond
     * @return string
     */
    private static function _buildQuery_fq(array $cond) {
        $query = '';
        switch ($cond[1]) {
            case '=':
                $query = "$cond[0]:$cond[2]";
                break;

            case '!=':
                $query = "-$cond[0]:$cond[2]";
                break;

            case '>':
                $query = "$cond[0]:\{$cond[2] TO *]";
                break;

            case '>=':
                $query = "$cond[0]:[$cond[2] TO *]";
                break;

            case '<':
                $query = "$cond[0]:[* TO $cond[2]\}";
                break;

            case '<=':
                $query = "$cond[0]:[* TO $cond[2]]";
                break;

            case 'in':
                $values = is_array($cond[2]) ? implode(' ', $cond[2]) : $cond[2];
                $query  = "$cond[0]:($values)";
                break;

            case 'nin':
                $values = is_array($cond[2]) ? implode(' ', $cond[2]) : $cond[2];
                $query  = "-$cond[0]:($values)";
                break;

            default:
                break;
        }

        return $query;
    }

    /**
     * 查询创建排序语法
     * @param array $sort
     * @return string
     */
    private static function _buildQuerySort(array $sort) {
        $query = [];
        foreach ($sort as $field => $mode) {
            $query[] = "$field " . ($mode == \Redb\Query::SORTMODE_ASC ? 'asc' : 'desc');
        }
        return implode(',', $query);
    }

}
