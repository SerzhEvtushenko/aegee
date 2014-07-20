<?php
/**
 * @package SolveProject
 * @subpackage Cache
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 01.11.2009 0:31:54
 */

/**
 * Access Control List class
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slCache {

    static private $_handlers = array();

    static private $_active_handler = null;


    static public function initialize($handler_name = 'FileCache') {

    }

    static public function activateHandler($handler_name) {

    }

    static public function purgeHandlerCache($handler_name) {

    }

    static public function set($key, $default = null) {

    }

    static public function get($key, $value) {
    
    }

}
