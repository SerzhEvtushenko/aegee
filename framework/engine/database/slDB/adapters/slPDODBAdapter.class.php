<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created: 15.11.2009 18:04:23
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * DB Adapter using PDO DB Layer
 * @26.10.2010 added where operator (like, gt, lt, ge, le, ne)
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slPDODBAdapter extends slDBAdapter {

    /**
     * @var PDO connection
     */
    protected $_dbh         = null;

    /**
     * @var array parameters for connection
     */
    protected $_profile     = array();

    protected $_profiler    = false;

    protected $_is_sqlite   = false;

    /**
     * @param mixed $options
     * @return bool
     * @throws Exception
     */
    public function connect($options) {
        if (!extension_loaded('pdo')) throw new Exception('PDO extension is not loaded.');
        try {
            $this->_dbh = new PDO(($dsn = self::compileDSN($options)), $options['user'], $options['pass']);
            if (strpos($dsn, 'sqlite:') === 0) {
                $this->_is_sqlite = true;
            }
            $this->_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            throw new slDBException('PDO Connection Error: ' . $e->getMessage());
        }

        try {
            if (!empty($options['dbname'])) {
                $this->_dbh->query('USE '.$options['dbname']);
            }
            $this->_dbh->setAttribute(PDO::ATTR_AUTOCOMMIT , true);
            if (!empty($options['charset'])) {
                $this->_dbh->query('SET NAMES '.$options['charset']);
            }
        } catch (PDOException $e) {
            throw new slDBException('PDO Connection Error: ' . $e->getMessage());
        }

        $this->_profiler = SL::getDatabaseConfig('profiler');
        return true;
    }

    /**
     * Retrun current datetime format, based on format from database
     * @param bool $force
     * @return mixed
     */
    public function getDateTimeFormat($force = true) {
        $profile_name = slDatabaseManager::getActiveProfile();
        $dt = SL::getDatabaseConfig('profiles/'.$profile_name);
        if (!isset($dt['datetime_format'])) {
            $res = $this->_dbh->query('SELECT @@datetime_format AS dtf, @@date_format AS df FROM DUAL')->fetch();
            $dt['datetime_format'] = str_replace('%', '', $res['dtf']);
            $dt['date_format'] = str_replace('%', '', $res['df']);
        }
        return $dt['datetime_format'];
    }

    /**
     * Activate or deactivate profiler
     * @param null $bool
     * @return bool
     */
    public function switchProfiler($bool = null) {
        if ($bool === null) {
            $this->_profiler = !$this->_profiler;
        } else {
            $this->_profiler = (bool)$bool;
        }
        return true;
    }

    /**
     * Finisher for query lance
     * @param $sql
     * @return PDOStatement
     */
    public function query($sql) {
        if ($this->_profiler) {
            slProfiler::startTimer($sql, 'db');
        }
        $res = $this->_dbh->query($sql);
        if ($this->_profiler) {
            slProfiler::stopTimer($sql, 'db');
        }

        return $res;
    }

    /**
     * Just select what type of query need to be runned
     * @param Q $q
     * @param bool $returnSQL
     * @return mixed
     * @throws slDBException
     */
    public function processQuery(Q $q, $returnSQL = false) {
        $method_name = 'do'.ucfirst($q->getType());
        if (!method_exists($this, $method_name)) {
            throw new slDBException('Method '.$method_name.' is not implemented');
        }

        return $this->$method_name($q, $returnSQL);
    }

    /**
     * Perform SELECT query and execute it
     * @throws DBException if no tables specified for selection
     * @param Q $q query to execute
     * @param bool $returnSQL generate sql and return it. Do not execute anything.
     * @return mixed result of selection
     */
    private function doSelect(Q $q, $returnSQL = false) {
        $this->requireTable($q);

        $c = $q->getCriteria();

        $sql = 'SELECT ';
        $fields = $q->getPart('fields');
        $sql .= count($fields) ? $this->escapeSqlName($fields) : '*';

        $sql .= ' FROM '.$this->escapeSqlName($q->getPart('tables'));

		foreach($q->getPart('joins') as $join) {
			$sql .= ' '.strtoupper($join['type']).' JOIN '.$join['table'].
					(strpos($join['on'], ' ') === false ? ' USING ('.$this->escapeSqlName($join['on']) : ' ON ('.$join['on']).')';
		}

		if (!$q->getCriteria()->isEmpty()) {
		    $sql .= (count($c->getCriteria()) ? ' WHERE '.$this->processCriteria($c->getCriteria()) : '');
		    $sql .= $this->processModifiers($c);
		}

        if ($returnSQL) return $sql;

		$res = $this->query($sql);
		$data =  array();

        if ($res->rowCount() || $this->_is_sqlite) {
            $rows = $res->fetchAll(PDO::FETCH_ASSOC);
			foreach($rows as $row) {
				$item = $row;
				if ($c->hasModifier('useValue')) {
					$item = isset($row[$c->getModifier('useValue')]) ? $row[$c->getModifier('useValue')] : null;
				}
				if ($c->hasModifier('indexBy') && isset($row[$c->getModifier('indexBy')])) $index_by =  $row[$c->getModifier('indexBy')];
				if ($c->hasModifier('foldBy')) $fold_by = isset($row[$c->getModifier('foldBy')]) ? $row[$c->getModifier('foldBy')] : false;

				if (isset($fold_by)) {
					if (isset($index_by)) {
						$data[$fold_by][$index_by] = $item;
					} else {
						$data[$fold_by][] = $item;
					}
				} elseif (isset($index_by)) {
					$data[$index_by] = $item;
				} else {
					$data[] = $item;
				}
			}
		}

		return $c->hasModifier('one') ? array_shift($data) : $data;
    }

    /**
     * Truncate tables specified in Q
     *
     * @throws DBException if no tables specified
     * @param Q $q
     * @param boolean $returnSQL
     * @return PDOResult query result
     */
    public function doTruncate(Q $q, $returnSQL = false) {
        $this->requireTable($q);

        $sql = 'TRUNCATE '.$this->escapeSqlName($q->getPart('tables'));
        if ($returnSQL) return $sql;

        return $this->query($sql);
    }

    /**
     * Perform DELETE query and execute it
     * @param Q $q
     * @param bool $returnSQL
     * @return PDOStatement|string
     */
    public function doDelete(Q $q, $returnSQL = false) {
        $this->requireTable($q);
        
        $sql = 'DELETE FROM '.$this->escapeSqlName($q->getPart('tables'));

		if (!$q->getCriteria()->isEmpty()) {
		    $sql .= (count($q->getCriteria()) ? ' WHERE '.$this->processCriteria($q->getCriteria()->getCriteria()) : '');
		    $sql .= $this->processModifiers($q->getCriteria());
		}
        if ($returnSQL) return $sql;
        
        return $this->query($sql);
    }

    /**
     * Perform INSERT query and execute it
     * @param Q $q
     * @param bool $returnSQL
     * @param bool $isReplace
     * @return bool|string
     */
    public function doInsert(Q $q, $returnSQL = false, $isReplace = false) {
        $this->requireTable($q);

        $sql = ($isReplace ? 'REPLACE' : 'INSERT').' INTO '.$this->escapeSqlName($q->getPart('tables')).' ';
        
        $keys = array();
        $data = $q->getPart('data');
        if (count($data)) {
            foreach($data as $i=>$item) {
                if (!is_array($item)) continue;

                if (array_key_exists(0, $item)) {
                    unset($data[$i]);
                    foreach($item as $sub_item) {
                        $keys = array_merge($keys, array_keys($sub_item));
                        $data[] = $sub_item;
                    }
                } else {
                    $keys = array_merge($keys, array_keys($item));
                }
            }
            $keys = array_unique($keys);
            if (count($keys)) {
                $sql .= '(`'.implode('`, `',$keys).'`) VALUES ';
                foreach($data as $i=>$item) {
                    $sql .= '(';
                    foreach($keys as $key) {
                        $sql .= (isset($item[$key]) ? $this->escapeOne($item[$key]) : '""').", ";
                    }
                    $sql = substr($sql, 0, -2).'), ';
                }
            } else {
                $sql.= 'SET ';
                foreach($q->getPart('data') as $item) $sql.= $item.',';
            }
            $sql = substr($sql, 0, -2);
        } else {
            $sql .= '() VALUES ()';
        }

		if (!$q->getCriteria()->isEmpty()) {
		    $sql .= $this->processModifiers($q->getCriteria());
		}

        if ($returnSQL) return $sql;
        $res = $this->query($sql);
        return $res ? $this->_dbh->lastInsertId() : false;
    }

    /**
     * Wrapper for Insert as Replace
     * @param Q $q
     * @param bool $returnSQL
     * @return bool|string
     */
    public function doReplace(Q $q, $returnSQL = false) {
        return $this->doInsert($q, $returnSQL, true);
    }

    /**
     * Perform and execute UPDATE query
     * @param Q $q
     * @param bool $returnSQL
     * @return PDOStatement|string
     */
    public function doUpdate(Q $q, $returnSQL = false) {
        $this->requireTable($q);
        $c = $q->getCriteria();
        
		$sql = 'UPDATE '.$this->escapeSqlName($q->getPart('tables'))
			.' SET '.$this->processCriteria($q->getPart('data'), ', ')
			;
		if (!$q->getCriteria()->isEmpty()) {
		    $sql .= (count($c->getCriteria()) ? ' WHERE '.$this->processCriteria($c->getCriteria()) : '');
		    $sql .= $this->processModifiers($c);
		}
		if ($returnSQL) return $sql;

		return $this->query($sql);
    }

    /**
     * Escape SQL identifiers with "`"
     * @param string|array $value identifiers or identifier for escaping
     * @return string Escaped SQL identifier
     */
	protected function escapeSqlName($value) {
		if (is_array($value)) {
			$res = '';
			foreach($value as $val) $res .= $this->escapeSqlName($val).',';
			return substr($res, 0, -1);
		}
		$chk_chars = array('.', '(', ' ', '*');
		foreach($chk_chars as $chr) {
			if (strpos($value, $chr) !== false) {
				return $value;
			}
		}
		return '`'.$value.'`';
	}

	/**
     * Escape on value – prepare it to insertion into SQL
     * @param mixed $value
     * @return mixed Escaped value
     */
    protected function escapeOne($value) {
		if (trim($value) == '' || (strcasecmp($value, 'null') === 0)) {
			return 'NULL';
		} elseif(substr($value, 0, 5)=='#sql#') {
			return substr($value, 5);
		} elseif(is_numeric($value)) {
            return (mb_strlen($value) > 0 && (mb_substr($value, 0, 1) == '0' || mb_substr($value, 0, 1) == '+'))
                ? $this->_dbh->quote($value)
                : $value;
		} elseif(is_string($value)) {
			return $this->_dbh->quote($value);
		}
		return $value;
    }

    /**
     * Prepare SQL part for WHERE and others
     * @param $c
     * @param string $split
     * @return string
     */
    protected function processCriteria($c, $split = ' AND ') {
        $sql = '';

        foreach($c as $key=>$criterion) {
            $operator = '=';
            if (is_array($criterion)) {
                if (isset($criterion['type']) && isset($criterion['params']) && isset($criterion['operator']) && count($criterion) == 3) {
                    $split = ' '.strtoupper($criterion['type']).' ';
                    $operator = $criterion['operator'];
                    $criterion = $criterion['params'];
                }
            }

            $sql .= $split;
            if (is_array($criterion)) {
                if (isset($criterion['__complex'])) {
                    $sql .= $this->processCriteria($criterion['__complex']);
                    continue;
                }
                switch($operator) {
                    case 'like':
                        $sub_res = $this->getOperatorLikeSQL($criterion, $split);
                        break;
                    default:
                    case '=':
                        $sub_res = $this->getOperatorEqualSQL($criterion, $split);
                        break;

                }
                $sql .= substr($sub_res, strlen($split));
            } else {
                $sql .= $criterion;
            }

        }
        $sql = '('.trim(substr($sql, strlen($split))).')';
        if (count($c) == 1) $sql = substr($sql, 1, -1);

        return $sql;
    }

    /**
     * Retrun SQL part for IN or =
     * @param $criterion
     * @param $split
     * @return string
     */
    private function getOperatorEqualSQL(&$criterion, $split) {
        $sub_res = '';
        foreach($criterion as $field=>$value) {
            $sub_res .= $split.$this->escapeSqlName($field);
            if (is_array($value)) {
                $sub_res .= ' IN ('.$this->escape($value).')';
            } else {
                $sub_res .= ' = '.$this->escapeOne($value);
            }
        }
        return $sub_res;
    }

    /**
     * Return SQL part for LIKE
     * @param $criterion
     * @param $split
     * @return string
     */
    private function getOperatorLikeSQL(&$criterion, $split) {
        $sub_res = $split . '(';
        $fields = is_array($criterion[0]) ? $criterion[0] : array($criterion[0]);
        foreach($fields as $field) {
            $sub_res .=  $this->escapeSqlName($field)
                        .' LIKE "%'.substr($this->escapeOne($criterion[1]), 1, -1).'%" OR ';
        }
        $sub_res = substr($sub_res, 0, -4) . ')';
        return $sub_res;
    }

    /**
     * Return SQL part for some modifiers
     * @param C $c
     * @return string
     */
    protected function processModifiers($c) {
        $sql = '';
        if ($c->hasModifier('groupBy')) $sql .= ' GROUP BY '.$c->getModifier('groupBy');
        if ($c->hasModifier('orderBy')) $sql .= ' ORDER BY '.$c->getModifier('orderBy');
        if ($c->hasModifier('one')) {
            $sql .= ' LIMIT 1';
        } elseif($c->hasModifier('limit')) {
            $limit = $c->getModifier('limit');
            if (is_array($limit)) $limit = implode(', ', $limit);
            $sql .= ' LIMIT '.$limit;
        }
        return $sql;
    }

    /**
     * Check if table is specified for query
     * @param Q $q
     * @throws slDBException
     */
    protected function requireTable(Q $q) {
        if (!count($q->getPart('tables'))) {
            throw new slDBException('Tables for '.strtoupper($q->getType()).' query must be specified');
        }
    }

    /**
     * Clear database handler
     * @return void
     */
    public function __destruct() {
		if (!is_null($this->_dbh)) {
        	$this->_dbh = null;
        }
    }

}
