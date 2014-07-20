<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 16.11.2009 22:20:37
 */

/**
 * Useful for paginate different sources. Also support Query and Array
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slPaginator {

    static private $_last_result = array();

    static public function getFromCollection(slModelCollection $collection, $current_page = 1, $on_page = 10) {
        $count = count($collection);
        $result = new slModelCollection($collection->getModelName());
        $pages_count = (int)ceil($count/$on_page);
        if (($current_page - 1) * $on_page > $count) throw new Exception('Page ['.$current_page.'/'.$pages_count.'] not found in paginator ['.($current_page - 1) * $on_page.'/'.$count.']');
        self::$_last_result = array(
            'current_page'  => $current_page,
            'on_page'       => $on_page,
            'pages_count'   => $pages_count,
            'results_count' => $count
        );
        $start = ($current_page - 1) * $on_page;
        $end = ($current_page) * $on_page;
        foreach($collection as $i => $model) {
            if ($i < $start) continue;
            if ($i >= $end) break;
            $result->addObject($model);
        }
        return $result;
    }

    static public function getFromQuery(Q $q, $current_page = 1, $on_page = 10, $id_field = null) {
        if ($q->getType() !== 'select') throw new Exception('You can use Qpaginator only with select queries.');
        $sql = $q->getSQL();

        $select_pos = strpos($sql, 'SELECT');
        $sql_com = substr($sql, 0, $select_pos).'SELECT SQL_CALC_FOUND_ROWS '.substr($sql, $select_pos+6).' LIMIT '.($current_page-1)*$on_page.', '.$on_page;
        $data = Q::execSQL($sql_com);

        $count = Q::execSQL('SELECT FOUND_ROWS() as cnt')->fetch(PDO::FETCH_ASSOC);
        if (!$data->rowCount() && $count['cnt']) throw new Exception('Page not found in paginator');

        $pages_count = (int)ceil($count['cnt']/$on_page);
        self::$_last_result = array(
            'current_page'  => $current_page,
            'on_page'       => $on_page,
            'pages_count'   => $pages_count,
            'results_count' => $count['cnt']
        );

        $data = $data->fetchAll(PDO::FETCH_ASSOC);
        if (!is_null($id_field)) {
            $res = array();
            foreach($data as $item) {
                $res[] = isset($item[$id_field]) ? $item[$id_field] : null;
            }
            $data = $res;
        }
        return $data;
    }

    static public function getInfo() {
        return self::$_last_result;
    }

}
