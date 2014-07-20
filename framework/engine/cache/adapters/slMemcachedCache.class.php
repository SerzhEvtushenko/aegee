<?php
/**
 * @package SolveProject
 * @subpackage Cache
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created Mar 26, 2012 19:13:21
 */

/**
 * Memcached Cache
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slMemcachedCache {

    private $_handler   = null;
    private $_data      = array();

    public function setHandler($path) {
        touch($path);
        if (!is_file($path)) throw new Exception('Can not switch to cache handler:'.$path);
        $this->_handler = $path;

        $content = file_get_contents($this->_handler);

        $this->_data = unserialize($content);
        if (!$this->_data) $this->_data = array();
    }

    public function get($key, $default = null) {
        return SL::getDeepArrayValue($this->_data, $key);
    }

    public function set($key, $value) {
        $this->_data[$key] = $value;
        return true;
    }

    public function clear() {
        $this->_data = array();
        $this->flush();
    }

    public function flush() {
        file_put_contents($this->_handler, serialize($this->_data));
    }

    public function __destruct() {
//        $this->flushBuffer();
    }

}
