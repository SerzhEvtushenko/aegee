<?php
/**
 * @package SolveProject
 * @subpackage Event
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 25.11.2009 8:32:14
 */

/**
 * slEvent class. Evey slEvent in project must be extended from this class
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slEvent implements Iterator, ArrayAccess, SeekableIterator, Countable {

    /**
     * @var string name using for working with event
     */
    private $_name   = '';
    /**
     * @var string store event current state
     */
    private $_state = null;

    // used for ArrayAccess, SeekableIterator methods and statuses
    private
        $_vars = array(),
        $_keys = array(),
        $_states = array('created', 'fired', 'stopped', 'finished'),
        $_pos  = 0,
        $_data = array();

    /**
     * Create slEvent with specified name
     * @param string $name
     * @param array $params
     */
    public function __construct($name, $params = array()) {
        $this->_name = $name;
        $this->_state = slEventState::CREATED;
        foreach($params as $key=>$value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * @return string current event name
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Stop event continuing. If method executed no more listeners will be notified
     * @return void
     */
    public function stopPropagation() {
        $this->setState(slEventState::STOPPED);
    }


    /**
     * Check if event is stopped
     * @see stopPropagation()
     * @return boolean is event stoped
     */
    public function isStopped() {
        return ($this->_state == slEventState::STOPPED);
    }

    /**
     * Check if event is finished
     * @return boolean is event stoped
     */
    public function isFinished() {
        return ($this->_state == slEventState::FINISHED);
    }

    /**
     * @param EventState constant
     * @return slEvent current instance
     */
    public function setState($state) {
        if (!in_array($state, $this->_states)) throw new ffEventException('Trying to set unimplemented state for Event ', $this->_name);
        $this->_state = $state;
        return $this;
    }

    public function getState() {
        return $this->_state;
    }



    // Service Functions
    public function offsetGet($pos) {
        return $this->_vars[$pos];
    }
    public function offsetSet($pos, $value) {
        if (!isset($this->_data[$pos])) {
            $this->_keys[] = $pos;
        }
        $this->_vars[$pos] = $value;
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

}
