<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 15.11.2009 14:39:42
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Operate with database engines and plugins
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slDatabaseManager {

    /**
     * @var slDBEngine current engine name
     */
    static private $_engine = null;

    /**
     * @var array stores connections for quick access in future requests
     */
    static private $_connections = array();

    /**
     * @var string using for getConnection
     */
    static private $_active_profile = null;

    /**
     * @static
     * @return void 
     */
    static public function initialize() {
        slProfiler::checkPoint('slDatabaseManager::initialize()');
        if (empty(self::$_engine) && ($engine_class = SL::getDatabaseConfig('engine', 'slDB'))) {
            $engine_class .= 'Engine';
            self::$_engine = new $engine_class();
        }
        if (empty(self::$_engine)) SL::log('slDatabaseManager can not be initialized', slLoggerNamespace::DB_NAMESPACE);
    }

    /**
     * Return connection object
     * @static
     * @param null $profile_name
     * @param bool $reconnect
     * @return slDBAdapter
     */
    static public function getConnection($profile_name = null, $reconnect = false) {
        if (!self::$_active_profile) self::$_active_profile = $profile_name ? $profile_name : SL::getDatabaseConfig('active_profile', 'default');
        if (!self::$_engine) self::initialize();

        if (empty(self::$_connections[self::$_active_profile]) || $reconnect) {
            self::setActiveProfile(self::$_active_profile);
        }
        return self::$_connections[self::$_active_profile];
    }

    /**
     * @static
     * @throws slDBException in case no profile data specified
     * @param string $profile_name name to activate
     * @param array $profile_data optional data for profile activation
     * @return void
     */
    static public function setActiveProfile($profile_name, $profile_data = array()) {
        self::initialize();
        if (self::$_active_profile == $profile_name && isset(self::$_connections[$profile_name])) return true;

        if (!count($profile_data)) {
            $profile_data = SL::getDatabaseConfig('profiles/'.$profile_name);
        }
        if (count($profile_data)) {
            self::$_connections[$profile_name] = self::$_engine->getConnection($profile_data);
            self::$_active_profile = $profile_name;
        } else {
            throw new slDBException('Cannot find data for profile '.$profile_name);
        }
    }

    /**
     * Return name of active profiles
     * @static
     * @return null|string
     */
    static public function getActiveProfile() {
        return self::$_active_profile;
    }

    /**
     * Switch profiling for specified profile or for current if not specified
     * @static
     * @param null $profile_name
     * @param null $state
     * @return bool
     */
    static public function switchProfiler($profile_name = null, $state = null) {
        if (!self::$_active_profile) self::$_active_profile = $profile_name ? $profile_name : SL::getDatabaseConfig('active_profile', 'default');
        self::setActiveProfile(self::$_active_profile);
        self::$_connections[self::$_active_profile]->switchProfiler($state);
        return true;
    }

}