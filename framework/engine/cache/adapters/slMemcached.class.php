<?php
/**
 * Created by JetBrains PhpStorm.
 * User: serg
 * Date: 27.08.12
 * Time: 17:03
 * To change this template use File | Settings | File Templates.
 */
class slMemcached {

    /**
     * @var $_handler Memcached
     */
    static protected $_handler      = null;
    static private $project_name    = '';

    static public function initialize() {
        if (is_null(self::$_handler) && class_exists('Memcached')) {
            self::$_handler = new Memcached();
            self::$_handler->addServer('localhost', 11211);
            self::$project_name = SL::getProjectConfig('name');
        }
    }

    static public function set($key, $value) {
        self::$_handler->set(self::$project_name . '_' . $key, $value);
    }

    static public function get($key) {
        return self::$_handler->get(self::$project_name . '_' . $key);
    }

    static public function getHandler() {
        return self::$_handler;
    }

}
