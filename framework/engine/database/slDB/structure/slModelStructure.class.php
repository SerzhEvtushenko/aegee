<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 01.12.2009 10:28:12
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Represent Structure of model as object
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slModelStructure implements Iterator, ArrayAccess, SeekableIterator, Countable {

    protected $_vars            = array();
    protected $_keys            = array();
    protected $_pos             = 0;

    protected $_model_name      = null;

    /**
     * Initialize with model name and structure
     * @param string $model_name
     * @param array|null $structure
     */
    public function __construct($model_name, $structure = null) {
        $this->_model_name = $model_name;
        if (!$structure) {
            $this->reloadStructure();
        } else {
            foreach($structure as $key=>$value) {
                $this[$key] = $value;
            }
        }
    }

    /**
     * Add columns if it's not exists for current model
     *
     * @param  $column_name
     * @param  $structure
     * @return boolean
     */
    public function addColumn($column_name, $structure) {
        if (!$this->isColumnExists($column_name)) {
            $this->_vars['columns'][$column_name] = $structure;
            $this->saveStructure();
            return true;
        }
        return false;
    }

    public function addAbility($ability_name) {
        $ability_key = slInflector::directorize($ability_name);
        $ability_class = $ability_name . 'Ability';
        if (!array_key_exists('abilities', $this->_vars)) {
            $this->_vars['abilities'] = array();
        }
        if (array_key_exists($ability_key, $this->_vars['abilities'])) {
            throw new slDBException('Ability '.$ability_name.' is already attached to '.$this->_model_name);
        }
        $this->_vars['abilities'][$ability_key] = call_user_func(array($ability_class, 'getInitialStructure'), $this);
        $this->saveStructure();
    }

    /**
     * Return structure from YAML for specified column name
     *
     * @param string $column_name
     * @return mixed column info if exists
     */
    public function getColumn($column_name) {
        return $this->isColumnExists($column_name) ? $this->_vars['columns'][$column_name] : null;
    }

    /**
     * Check if columns is exists
     * @param string $column_name
     * @return booelan
     */
    public function isColumnExists($column_name) {
        return isset($this->_vars['columns'][$column_name]);
    }

    /**
     * Reloading structure for current model from YML file
     */
    public function reloadStructure() {
        $this->_vars    = array();
        $this->_keys    = array();
        $this->_pos     = 0;
        $structure      = slDBOperator::getInstance()->getYamlStructure($this->_model_name);
        foreach($structure as $key=>$value) {
            $this[$key] = $value;
        }
    }

    /**
     * Update YML file from current object and generate new base model class
     */
    public function saveStructure() {
        slDBOperator::getInstance()->saveYamlStructure($this->_model_name, $this->_vars);
        slDBOperator::getInstance()->generateModelClass($this->_model_name, false);
    }

    /**
     * Return field specified as primary key or null
     * @return string|null
     */
    public function getPrimaryField() {
        if (isset($this->_vars['indexes']['primary']['columns']) && (count($this->_vars['indexes']['primary']['columns']) == 1)) {
            return $this->_vars['indexes']['primary']['columns'][0];
        } else {
            return null;
        }
    }

    /**
     * Return current model's class name
     * @return string
     */
    public function getClassName() {
        return $this->_model_name;
    }

    /**
     * Set vars via deep array value
     * @param mixed $value
     * @param string|null $what
     */
    public function set($value, $what = null) {
        SL::setDeepArrayValue($this->_vars, $value, $what);
    }

    /**
     * Return vars via deep array value
     * @param string|null $what
     * @return mixed
     */
    public function get($what = null) {
        return SL::getDeepArrayValue($this->_vars, $what);
    }

     //A lot of servie functions
    public function offsetGet ($key) {
        return isset($this->_vars[$key]) ? $this->_vars[$key] : null;
    }

    public function offsetSet($key, $value) {
        if (!isset($this->_vars[$key])) {
            $this->_keys[] = $key;
        }
        $this->_vars[$key] = $value;
    }
    public function offsetExists($pos) {
        return isset($this->_vars[$pos]);
    }
    public function offsetUnset($pos) {
        unset($this->_vars[$pos]);
        foreach($this->_keys as $key=>$value) {
            if ($value == $pos) unset($this->_keys[$key]);
        }
    }
	public function rewind () { $this->_pos = 0; }
	public function current () { return $this->_vars[$this->_keys[$this->_pos]]; }
	public function key () { return $this->_keys[$this->_pos]; }
	public function next () { $this->_pos++; }
	public function valid () { return isset($this->_keys[$this->_pos]); }
	public function seek($pos) { $this->_pos = array_search($pos, $this->_keys);}
	public function count() { return count($this->_vars); }
    public function __toString() { return $this->_model_name;}
}
