<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 15.11.2009 17:28:27
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
/**
 * Abstract class for creating adapters for slDBEngine
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
abstract class slDBAdapter {

    /**
     * current handler to DB
     */
	protected $_dbh     = null;

    /**
     * Create instance and trying to connect to DB
     * @param $options
     */
	public function __construct($options) {
	    if (!$options) throw new slDBException('Options for connection doesn\'t specified!');

		if ($this->connect($options) !== true) {
			throw new slDBException('Cannot connect to Database via Adapter');
		}
	}

    /**
     * Should be realized connect to database
     * @param mixed $options for connection
     */
	abstract public function connect($options);

    /**
     * Execute query
     * @abstract
     * @param $query
     */
	abstract public function query($query);

    /**
     * Parse query and execute it
     * @abstract
     * @param Q $q
     * @param bool $returnSQL
     */
	abstract public function processQuery(Q $q, $returnSQL = false);

    /**
     * Parse database config and prepare DSN
     * @todo delete method
     * @static
     * @param $dsn
     * @return array
     */
	static public function parseDSNold($dsn) {
	    $dbtype = '';
	    if (false !== ($pos = strpos($dsn, ':'))) {
	        $dbtype = substr($dsn, 0, $pos);
	        $dsn = substr($dsn, $pos+1);
	    }
	    $dsn = explode(';', $dsn);
	    $params = array();
	    $params['dbtype'] = $dbtype;
	    foreach($dsn as $item) {
	        $tmp = explode('=', $item);
	        $params[$tmp[0]] =$tmp[1];
	    }
	    return $params;
	}

    /**
     * Parse database config and prepare DSN
     *
     * @static
     * @param @params part of database config
     * @return array
     */
	static public function compileDSN(&$params) {
        $dsn = '';
        if (!isset($params['user'])) $params['user'] = null;
        if (!isset($params['pass'])) $params['pass'] = null;
        if (!isset($params['dbtype'])) $params['dbtype'] = 'mysql';

        if (!empty($params['dsn'])) {
            $dsn = $params['dsn'];
        } else {
            $dsn = $params['dbtype'] . ':host='.$params['host'];
        }
        //        $dsn = $params['dbtype'] . '://' . $params['user'] . ($params['pass'] ? ':' .$params['pass'] : '') . '@' . $params['host'];
        //        mysql:host=127.0.0.1;dbname=f_loboda
        return $dsn;
    }

    /**
     * Escape on value using database driver specific
     * @abstract
     * @param mixed $value to be escaped
     * @return mixed escaped
     */
	abstract protected function escapeOne($value);
	
    /**
     * Escape values for using in queries
     *
     * @param mixed $value to be escaped
     * @return mixed $value  Escaped
     */
	public function escape($value) {
		if (is_array($value)) {
			$res = '';
            if (count($value) == 0) return 'null';
			foreach($value as $val) {
				$res .= $this->escapeOne($val).',';
			}
			$res = substr($res, 0, -1);
			return $res;
		} else {
			return $this->escapeOne($value);
		}
	}

    /**
     * Escape identifiers
     * @param $value
     * @return string
     */
	protected function escapeSqlName($value) {
		if (is_array($value)) {
			$res = '';
			foreach($value as $val) $res .= $this->escapeSqlName($val).',';
			return substr($res, 0, -1);
		}
		$chk_chars = array('.', '(', ' ');
		foreach($chk_chars as $chr) {
			if (strpos($value, $chr) !== false) {
				return $value;
			}
		}
		return '`'.$value.'`';
	}

    /**
     * By default, throw exception. You need to clear connection to DB in destructor of adapter
     */
	public function __destruct() {
		throw new slDBException('Connection need to be cleared in desctructor of slDBAdapter!');
	}
}

?>