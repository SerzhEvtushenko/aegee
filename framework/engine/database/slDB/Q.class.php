<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 15.11.2009 14:29:56
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Using for operate with database
 *
 * @version 1.0
 *
 * @method Q query($sql) query() Using for execute RAW SQL queries
 * @method Q select($what = '*')) select() Set query type to select. Specifying what fields to select from DB Default selects all fields
 * @method Q insert($data) insert() Set query type to insert. Specifying data for inserting into DB
 * @method Q replace($data) replace() Set query type to insert. Specifying data for replacing into DB
 * @method Q update($data) update() Set query type to update. Specifying data for updating into DB
 * @method Q delete($criteria = null) delete() Set query type to delete. Specifying criteria for deleting
 *
 * @method Q from($table) from() Specify table for operating with
 * @method Q into($table) into() Specify table for operating with
 *
 * @method Q leftJoin($table, $criteria) leftJoin() add LEFT JOIN statement to query
 * @method Q rightJoin($table, $criteria) rightJoin() add RIGHT JOIN statement to query
 *
 * @method Q andWhere($criteria) andWhere() alias of C::andWhere
 * @method C andWhereLike($criteria) andWhereLike() alias of C::andWhereLike
 * @method C orWhereLike($criteria) orWhereLike() alias of C::andWhereLike
 * @method Q where($criteria) where() alias of C::where
 * @method Q orWhere($criteria) orWhere() alias of C::orWhere
 * @method Q one() one() alias of C::one
 * @method Q orderBy(string $field) orderBy() alias of C::orderBy
 * @method Q foldBy(string $field) foldBy() alias of C::foldBy
 * @method Q indexBy(string $field) indexBy() alias of C::indexBy
 * @method Q useValue(string $field) useValue() alias of C::useValue
 * @method Q limit(int $count, int $start = 0) limit() alias of C::limit
 * @method Q groupBy(string $field) groupBy() alias of C::groupBy
 *
 */
class Q {

    const HYDRATE_ARRAY     = 'q_hydrate_array';

    /**
     * @var slDBAdapter Engine using for current DB operating
     */
    private $_connection    = null;

    /**
     * @var C Criteria for current Query
     */
    private $_criteria      = null;

    private $_operators = array(
        'from'      =>  'tables',
        'into'      =>  'tables',
        
        'leftJoin'  =>  array(
            'paramsHandler' => 'getJoinParameters',
            'part'          => 'joins'
        ),
        'rightJoin'  => array(
            'paramsHandler' => 'getJoinParameters',
            'part'          => 'joins'
        ),


        'select'    => array(
            'part'          => 'fields',
            'switchType'    => 'select'
        ),
        'insert'    => array(
            'part'          => 'data',
            'switchType'    => 'insert'
        ),
        'replace'    => array(
            'part'          => 'data',
            'switchType'    => 'replace'
        ),
        'update'    => array(
            'part'          => 'data',
            'switchType'    => 'update'
        ),
        'delete'    => array(
            'part'          => 'andWhere',
            'switchType'    => 'delete'
        ),
        'truncate'  => array(
            'part'          => 'tables',
            'switchType'    => 'truncate'
        ),

        'where'     => array(
            'part'          => 'andWhere',
        ),
        'andWhere'      => array(
            'part'          => 'andWhere',
        ),
        'orWhere'       => array(
            'part'          => 'orWhere',
        ),
        'query'     =>  array(
            'part'          => 'query',
        )

    );

    /**
     * @var string store current type of query
     */
    private $_type      = 'select';

    /**
     * @var string current type of hydration
     */
    private $_hydration = Q::HYDRATE_ARRAY;

    /**
     * @var array parts of current query
     */
    private
        $_parts         = array(
            'tables'    => array(),
            'fields'    => array(),
            'joins'     => array(),
            'data'      => array(),
            'query'     => array(),
        );

    /**
     * @var slDBAdapter
     */
    static private $_static_handler = null;

    /**
     * @param string $profile_name to start with
     */
    public function __construct($profile_name = null) {
        $this->_connection = slDatabaseManager::getConnection($profile_name);
		$this->_criteria = new C();
    }

    /**
     * Using for inline creation
     * @example Q::create('users')->exec()
     *
     * @static
     * @param string $table optional parameters for specifying table for query
     * @return Q an instance of Q
     */
    static public function create($table = null) {
        $q = new Q();
        if ($table) $q->from($table);
        return $q;
    }

    /**
     * Format string as date time
     * @static
     * @param $string
     * @return string
     */
    static public function formatDate($string) {
        if (self::$_static_handler == null) {
            self::$_static_handler = slDatabaseManager::getConnection();
        }
        $format = self::$_static_handler->getDateTimeFormat();
        return date($format, strtotime($string));
    }

    /**
     * Format string as date time from timestamp
     * @static
     * @param $string
     * @return string
     */
    static public function formatDateFromTimestamp($string) {
        if (self::$_static_handler == null) {
            self::$_static_handler = slDatabaseManager::getConnection();
        }
        $format = self::$_static_handler->getDateTimeFormat();
        return date($format, $string);
    }

    /**
     * Executing SQL statement rapidly
     *
     * @static
     * @param string|mixed $raw_sql
     * @return mixed
     */
    static public function execSQL($raw_sql) {
        if (self::$_static_handler == null) {
            self::$_static_handler = slDatabaseManager::getConnection();
        }
        $res = null;
        if (!is_array($raw_sql)) $raw_sql = array($raw_sql);
        foreach($raw_sql as $sql) {
            $res = self::$_static_handler->query($sql);
        }
        return $res;
    }

    /**
     * Exec current query via active adapter
     * @param mixed $prepared_values
     * @return mixed
     */
    public function exec($prepared_values = null) {
        if (!empty($this->_parts['query'])) {
            $res = null;
            foreach($this->_parts['query'] as $one) {
                $res = $this->_connection->query($one);
            }
            $this->_parts['query'] = array();
            return $res;
        }
        return $this->_connection->processQuery($this);
    }

    /**
     * Replace current criteria with specified
     * @param C $c
     * @return Q
     */
    public function setCriteria(C $c) {
        $this->_criteria = $c;
        return $this;
    }

    /**
     * @return C current Criteria
     */
    public function getCriteria() {
        return $this->_criteria;
    }

    /**
     * Generate SQL from current query via adapter and return it
     * @return string Generated SQL
     */
    public function getSQL() {
        return $this->_connection->processQuery($this, true);
    }

    /**
     * Getter for parts of query
     *
     * @param string $what What part return, all if not specified
     * @return mixed values of parts
     */
    public function getPart($what = null) {
        if (!$what) {
            return $this->_parts;
        } else {
            return isset($this->_parts[$what]) ? $this->_parts[$what] : null;
        }
    }

    /**
     * Merge specified query with current
     *
     * @param Q $q
     * @return Q current query
     */
    public function merge(Q $q) {
        foreach($parts = $q->getPart() as $part=>$info) {
            if (!empty($info)) {
                if (is_array($info) && (count($info) == 1) && array_key_exists(0, $info)) {
                    $info = $info[0];
                }
                $this->addPart($part, $info);
            }
        }
        $this->where($q->getCriteria());
        return $this;
    }

    /**
     * Return type of current query
     * @return string Current type
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * Escape value using current DBAdapter
     * @static
     * @param $value
     * @return mixed
     */
    public static function escape($value) {
        return slDatabaseManager::getConnection()->escape($value);
    }


    /**
     * Magic method
     * @param $method
     * @param $params
     * @return Q
     * @throws Exception
     */
    public function __call($method, $params) {

        if (!empty($this->_operators[$method])) {

            $part = is_array($this->_operators[$method]) ? $this->_operators[$method]['part'] : $this->_operators[$method];

            if (is_array($this->_operators[$method])) {
            
                if (isset($this->_operators[$method]['paramsHandler'])) {
                    if (!method_exists($this, $this->_operators[$method]['paramsHandler'])) {
                        throw new Exception('Handler '.$this->_operators[$method]['paramsHandler'].' specified isn\'t exists. Using default.');
                    } else {
                        $this->addPart($part, $this->{$this->_operators[$method]['paramsHandler']}($method, $params));
                    }
                } else {
                    $this->addPart($part, $this->parseCallParameters($params));
                }

                if (isset($this->_operators[$method]['switchType'])) {
                    $this->_type = $this->_operators[$method]['switchType'];
                }

            } else {
                $this->addPart($part, $this->parseCallParameters($params));
            }
            return $this;
        }

        if ($this->_criteria->isModifierAvailable($method) || $this->_isWhereMethod($method)) {
            $this->_criteria->__call($method, $this->parseCallParameters($params));
        } else {
            $this->throwNotImplemented($method);
        }

        return $this;
    }

    /**
     * Check for 'where' type of query
     * @param $method
     * @return bool
     */
    private function _isWhereMethod($method) {
        return (bool)preg_match('#(and|or)?where.*#i', $method);
    }

    /**
     * Parse parameters for __call
     * @param $params
     * @return mixed
     */
    private function parseCallParameters($params) {
        if (count($params) == 1 && isset($params[0])) {
            return $params[0];
        }

        return $params;
    }

    /**
     * Add $data to $part and executing some specific operations 
     *
     * @access private
     * @param string $part
     * @param mixed $data
     * @return Q
     */
    private function addPart($part, $data) {
        if (empty($data) || count($data) == 0) return $this;

        if ($part == 'andWhere' || $part == 'orWhere') {
            $this->_criteria->__call($part, $data);
            return $this;
        }

        if (!in_array($data, $this->_parts[$part])) {
            $this->_parts[$part][] = $data;
        }
        return $this;
    }

    /**
     * @throws Exception if $method called is not implemented
     * @param string $method which is not implemented
     * @return void
     */
    private function throwNotImplemented($method) {
        throw new Exception('Method '.$method.' is not implemented in class Q yet!'."\n".' Currently implemented: '.implode(', ', array_keys($this->_operators)));
    }
    /**
     * Check for called method got 2 parameters
     *
     * @throws Exception if parameters count != 2
     * @param string $method called method name
     * @param mixed $params params given to method
     * @return mixed params
     */
    private function requireTwoParameters($method, $params) {
        if (count($params) != 2) {
            throw new Exception($method.' method require exactly 2 parameters and it need to be valid SQL identifier, but '.count($params).' got');
        }
        return $params;
    }

    /**
     * Check and transform parameters for Left and Right join
     *
     * @param string $method called method name
     * @param mixed $params params given to method
     * @return array Parameters transformed for Joins
     */
    private function getJoinParameters($method, $params) {
        $params = $this->requireTwoParameters($method, $params);

        return array(
            'type'  => substr($method, 0, -4),
            'table' => $params[0],
            'on'    => $params[1]
        );
    }

}
