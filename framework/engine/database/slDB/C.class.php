<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 13.11.2009 15:13:29
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Using for operate with different criteria in whole project
 * Provide all criteria methods for operations with DB
 *
 * @version 1.0
 *
 * @method C indexBy(string $key) indexBy() set returns array keys in value of $key
 * @method C foldBy(string $key) foldBy() set returns to grouped by $key arrays
 * @method C useValue(string $key) field() set returns values to specific $key
 *
 * @method C andWhere($criteria) andWhere() add AND criteria for current instance of Criteria
 * @method C andWhereLike($criteria) andWhereLike() add AND criteria for current instance of Criteria
 * @method C orWhereLike($criteria) orWhereLike() add AND criteria for current instance of Criteria
 * @method C where($criteria) where() alias for andWhere()
 * @method C orWhere($criteria) orWhere() add OR criteria for current instance of Criteria
 * @method C groupBy(string $criteria) groupBy() add GROUP BY statement for current instance of Criteria
 * @method C orderBy(string $criteria) orderBy() add ORDER BY statement for current instance of Criteria
 * @method C one() one() set for return only one first row of found set
 *
 */
class C {

    const
        C_AND     = 'and',
        C_OR      = 'or';

    /**
     * @var array modifiers are used in processing criteria
     */
    private $_modifiers         = array();

    /**
     * @var array criteria are used in processing criteria
     */
    private $_criteria          = array();


    /**
     * @var array using for __call method
     */
    private
        $_operators = array(
            'indexBy'   =>  array(
                'paramsHandler'    =>  'requireOneIdentifier'
            ),
            'foldBy'   =>  array(
                'paramsHandler'    =>  'requireOneIdentifier'
            ),
            'useValue'   =>  array(
                'paramsHandler'    =>  'requireOneIdentifier'
            ),
            'one'       => true,

            'unique'    => true,
            'limit'     => true,
            'groupBy'   => true,
            'orderBy'   => true,

        );


    /**
     * Using for inline creation
     * @example C::create()->and_where('1=1')
     *
     * @static
     * @param mixed $and_where_params optional parameters for andWhere-on-creation
     * @return C an instance of C
     */
    static public function create($and_where_params = null) {
        $c = new C();
        if (null !== $and_where_params) $c->andWhere($and_where_params);
        return $c;
    }

    /**
     * @param string $method called method name
     * @param $params Array with parameters
     * @return C
     */
    public function __call($method, $params) {
    
        if (!empty($this->_operators[$method])) {
            if (is_array($this->_operators[$method]) && isset($this->_operators[$method]['paramsHandler'])) {
                if (!method_exists($this, $this->_operators[$method]['paramsHandler'])) {
                    throw new Exception('Handler '.$this->_operators[$method]['paramsHandler'].' specified isn\'t exists. Using default.');
//                    $this->_modifiers[$method] = $this->parseCallParameters($params);
                } else {
                    $this->_modifiers[$method] = $this->{$this->_operators[$method]['paramsHandler']}($method, $params);
                }
            } else {
                $this->_modifiers[$method] = $this->parseCallParameters($params);
            }
            return $this;
        }

        $m = null;
        preg_match('#(?P<criteria_type>and|or)?(?P<method>where)(?P<operator>.*)#i',$method, $m);
        if (!count($m)) {
            $this->throwNotImplemented($method);
        }

        switch(strtolower($m['method'])) {
            case 'where':
                if ($params = $this->parseCallParameters($params)) {
                    $this->_criteria[] = array('type' => $m['criteria_type'] ? $m['criteria_type'] : C::C_AND, 'params' => $params, 'operator'=>$m['operator'] ? strtolower($m['operator']) : '=');
                }
                break;
            default:
                $this->throwNotImplemented($method);
            break;
        }

        return $this;
    }

    /**
     * Return empties of ciretria
     * @return bool is Criteria Empty
     */
    public function isEmpty() {
        return (count($this->_criteria) + count($this->_modifiers) == 0) ? true : false;
    }

    /**
     * Check for modifier was set
     *
     * @param string $name
     * @return boolean is modifier $name was set for current instance
     */
    public function hasModifier($name) {
        return isset($this->_modifiers[$name]);
    }

    /**
     * Return specified modifier, all if not specified
     *
     * @param string $name
     * @return array|null
     */
    public function getModifier($name = null) {
        if (!$name) {
            return $this->_modifiers;
        } else {
            return isset($this->_modifiers[$name]) ? $this->_modifiers[$name] : null; 
        }
    }

    /**
     * Setting modifier value
     *
     * @throws Exception if trying to set Modifier which not implemented yet
     * @param string $name Name of modifiers to set
     * @param mixed $value Value will set to modifier
     * @return C
     */
    public function setModifier($name, $value = true) {
        if (empty($this->_operators[$name])) {
            $this->throwNotImplemented($name);
        }

        $this->_modifiers[$name] = $value;        
        return $this;
    }

    /**
     * Check for implementation of operator. Used in updates
     * @param string $method
     * @return boolean is exists operator
     */
    public function isModifierAvailable($method) {
        return !empty($this->_operators[$method]);
    }

    /**
     * Return current criteria
     * @return array currently set criteria
     */
    public function getCriteria() {
        return $this->_criteria;
    }

    /**
     * Parsing all parameters for __call method
     * @param $params
     * @return array|bool
     */
    private function parseCallParameters($params) {
        if (is_array($params) && count($params) == 1) {
            if (array_key_exists(0, $params)) {
                if ($params[0] instanceof C) {
                    $this->_modifiers = array_merge($this->_modifiers, $params[0]->getModifier());
                    return array('__complex' => $params[0]->getCriteria());
                } else {
                    return $params[0];
                }
            }
        }
        if ($params instanceof C) {
            $this->_modifiers = array_merge($this->_modifiers, $params->getModifier());
            if ($criteria = $params->getCriteria()) {
                return  array('__complex' => $criteria);
            } else {
                return false;
            }
        }

        return $params;
    }

    /**
     * @throws Exception if $method called is not implemented
     * @param string $method which is not implemented
     * @return void
     */
    private function throwNotImplemented($method) {
        throw new Exception('Method '.$method.' is not implemented in class C yet!'."\n".' Currently implemented: '.implode(', ', array_keys($this->_operators)));
    }

    /**
     * Check for params is one valid SQL identifier
     *
     * @throws Exception if invalid parameters got
     * @param string $method method executed
     * @param array $params params given to method
     * @return string Parameter
     */
    private function requireOneIdentifier($method, $params) {
        if (!is_array($params)) $params = array($params);
        if (count($params) != 1 || !self::isValidIdentifier($params[0])) {
            throw new Exception($method.' method require exactly 1 parameter and it need to be valid SQL identifier, but "'.(string)$params[0].'" got');
        }
        return $params[0];
    }

    /**
     * Check parameter for validness as DB identifier
     * @static
     * @param $identifier
     * @return int
     */
    static public function isValidIdentifier($identifier) {
        $reg = '#^[-_a-z\.`]+[-_a-z\.`0-9]*$#isU';
        return preg_match($reg, $identifier);
    }

    
}
