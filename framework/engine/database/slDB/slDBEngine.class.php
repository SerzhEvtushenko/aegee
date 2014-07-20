<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 15.11.2009 14:52:08
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Engine adapter
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slDBEngine {

    /**
     * @var array store all active connections
     */
    private $_connections       = array();

    /**
     * @var string store active adapter class name
     */
    private $_adapter_class     = null;

    /**
     * Also check for adapter class aviability
     */
    public function __construct() {
        $this->_adapter_class = 'sl'.ucfirst(SL::getDatabaseConfig('adapter', 'PDO')) . 'DBAdapter';

        if (!class_exists($this->_adapter_class)) {
            throw new Exception('Database Adapter '. $this->_adapter_class  . ' not found!');
        }
    }

    /**
     * @param  $profile
     * @return slDBAdapter
     */
    public function getConnection($profile) {
        if (!isset($this->_connections[serialize($profile)])) {
            $this->_connections[serialize($profile)] = new $this->_adapter_class($profile);
        }
        return $this->_connections[serialize($profile)];
    }

}
