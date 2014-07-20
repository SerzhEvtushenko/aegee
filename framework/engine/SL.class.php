<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 23.11.2009 23:52:22
 */

/**
 * Initialize bootstrap parameters
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class SL {

    /**
     * @var string Active Application name
     */
    static private $_active_app_name            = null;

    /**
     * @var slApplication store current slApplication instance
     */
    static private $_active_application         = null;

    /**
     * @var array Configs of all project applications
     */
    static private $applications_configs        = array();

    /**
     * @var array Contains all loaded config (project, database, etc.)
     */
    static private $loaded_configs              = array();

    /**
     * @var array Contains some vars calculated on load
     */
    static private $vars                        = array();

    /**
     * @var bool is SL initialize executed
     */
	static private $_initialized                = false;

    /**
     * @var slLogger logger
     */
    static private $_logger                     = null;

    /**
     * Initialize directories path, DB, Imgae, MLT engines
     *
     * @static
     * @param null $root_folder if root folder is different
     * @return bool
     */
	static public function initialize($root_folder = null) {
        slProfiler::checkPoint('SL::initialize()');
        // prevent double initialization
        if (self::$_initialized) return true;

        set_exception_handler((array('slDebug', 'exceptionHandler')));
        set_error_handler(array('slDebug', 'errorHandler'));
        self::$_logger                      = new slLogger();

        self::initializeDirectories($root_folder);
        date_default_timezone_set(SL::getConfig('project', 'timezone', 'Europe/Kiev'));

        if (!SL::getProjectConfig('dev_mode')) {
            error_reporting(0);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        }

        slDatabaseManager::initialize();
        slImage::initialize();
        MLT::initialize();
        return (self::$_initialized = true);
	}

    /**
     * Fills vars with actual directories paths
     *
     * @static
     * @param $root_folder
     */
    static public function initializeDirectories($root_folder) {
        self::$vars['dir']['root']          = realpath($root_folder ? $root_folder  : dirname(__FILE__). '/../../') . '/';
        self::$vars['dir']['framework']     = realpath(dirname(__FILE__). '/../../') . '/framework/';
        self::$vars['dir']['libs']          = self::$vars['dir']['framework']   . 'libs/';
        self::$vars['dir']['user_libs']     = self::$vars['dir']['root']        . 'libs/';
        self::$vars['dir']['plugins']       = self::$vars['dir']['user_libs']   . 'plugins/';
        self::$vars['dir']['engine']        = self::$vars['dir']['framework']   . 'engine/';

        self::$vars['dir']['tmp']           = self::$vars['dir']['root']   . 'tmp/';
        self::$vars['dir']['web']           = self::$vars['dir']['root']   . self::getProjectConfig('web_root', 'web') . '/';
        self::$vars['dir']['upload']        = self::$vars['dir']['web']    . self::getProjectConfig('upload_dir','upload'). '/';
        self::$vars['dir']['cache']         = self::$vars['dir']['tmp']    . 'cache/';
        self::$vars['dir']['log']           = self::$vars['dir']['tmp']    . 'log/';

        // to make windows paths look like unix
        if (self::isWindows()) {
            foreach (self::$vars['dir'] as $key => $value) {
                self::$vars['dir'][$key] = str_replace('\\','/',$value);
            }
        }
        // autoload user libs
        slAutoloader::getInstance()->addDir(self::$vars['dir']['user_libs'], true);
        slLocator::makeWritable(self::$vars['dir']['tmp']);
        slLocator::makeWritable(self::$vars['dir']['log']);
        slLocator::makeWritable(self::$vars['dir']['upload']);
        slLocator::makeWritable(self::$vars['dir']['cache']);
    }

    /**
     * Log message to the specified namespace to the log file
     * @static
     * @param $message
     * @param string $namespace
     */
	static public function log($message, $namespace = slLoggerNamespace::APPLICATION_NAMESPACE) {
	    self::$_logger->add($message, $namespace);
	}

    /**
     * Return logs from current user request
     * @static
     * @param mixed $namespace
     * @param string $search
     * @return mixed logs
     */
	static public function getLogs($namespace = null, $search = null) {
	    return self::$_logger->getAll($namespace, $search);
	}

    /**
     * @static
     * @param string $name Variable for return
     * @return mixed value of var
     */
    static public function getVar($name) {
        return self::getDeepArrayValue(self::$vars, $name);
    }

    static public function getLogger() { return self::$_logger; }

    static public function getDirRoot() { return self::$vars['dir']['root']; }
    static public function getDirFramework() { return self::$vars['dir']['framework']; }
    static public function getDirEngine() { return self::$vars['dir']['engine']; }
    static public function getDirTmp() { return self::$vars['dir']['tmp']; }
    static public function getDirLog() { return self::$vars['dir']['log']; }
    static public function getDirCache() { return self::$vars['dir']['cache']; }
    static public function getDirLibs() { return self::$vars['dir']['libs']; }
    static public function getDirUserLibs() { return self::$vars['dir']['user_libs']; }
    static public function getDirPlugins() { return self::$vars['dir']['plugins']; }
    static public function getDirWeb() { return self::$vars['dir']['web']; }
    static public function getDirUpload() { return self::$vars['dir']['upload']; }

    /**
     * Create instance of slApplication specified in $route param
     *
     * @param $application_name
     * @return slApplication
     */
	static public function loadApplication($application_name) {
        slProfiler::checkPoint('SL::loadApplication');
        switch (slRouter::getCurrentMode()) {
            case slRouter::MODE_CONSOLE:
            case slRouter::MODE_WEB:
            default:
                self::$_active_application = new slApplication($application_name);
                break;
        }
        return self::$_active_application;
	}

    /**
     * @return slApplication current loaded application
     */
	static public function getApplication() {
	    return self::$_active_application;
	}


    /**
     * @static
     * @param string $what Key for search in config
     * @param string $app_name
     * @return mixed value of key specified in what
     */
    static public function getApplicationConfig($what = null, $app_name = null) {
        if (!$app_name) {
            if (!self::$_active_app_name) self::$_active_app_name = self::getProjectConfig('default_application');
            $app_name = self::$_active_app_name;
        }
        if (empty(self::$applications_configs[$app_name])) {
            self::loadApplicationConfig($app_name);
        }

        return self::getDeepArrayValue(self::$applications_configs[$app_name], $what);
    }

    static public function setApplicationConfig($value, $what = null, $app_name = null) {
        if (!$app_name) $app_name = self::$_active_app_name;
        if (!isset(self::$applications_configs[$app_name]) && $what) self::loadApplicationConfig($app_name);

        self::setDeepArrayValue(self::$applications_configs[$app_name], $value, $what);

        if (!$what) {
            self::$applications_configs[$app_name]['name']  = $app_name;
            if (!isset(self::$applications_configs[$app_name]['dir'])) {
                self::$applications_configs[$app_name]['dir']   = self::$vars['dir']['root'] . 'apps/' . $app_name . '/';
            }
        }
    }

    /**
     * Change active application to specified
     * @static
     * @param string $app_name
     */
    static public function setActiveApplication($app_name) {
        self::$_active_app_name = $app_name;
    }

    /**
     * Loading application config to the system
     * @static
     * @param string $app_name
     */
    static private function loadApplicationConfig($app_name) {
        $default_config_file = self::$vars['dir']['engine'] . 'defaults/'. slRouter::getCurrentMode() .'/application.yml';

        $config_file = self::$vars['dir']['root'] . 'apps/' . $app_name . '/config/application.yml';
        $default_config = sfYaml::load($default_config_file);

        if (is_file($config_file)) {
            $config_data = sfYaml::load($config_file);
            self::extendDeepArrayValue($default_config, $config_data);
        }

        self::$_active_app_name      = $app_name;
        self::setApplicationConfig($default_config);
    }

    /**
     * Getting config (or some part of). If is set local config, getting mix of default and local configs.
     * @static
     * @param $config_name
     * @param mixed $what
     * @param mixed $default
     * @param bool $force_reload
     * @return mixed config
     */
    static public function getConfig($config_name, $what = null, $default = null, $force_reload = false) {

        if (empty(self::$loaded_configs[$config_name])||$force_reload) {

            $default_path   = self::$vars['dir']['root'] . 'config/'.$config_name.'.yml';
            $local_path     = self::$vars['dir']['root'] . 'config/local/'.$config_name.'.yml';
            if(file_exists($default_path)) {
                $config_data = sfYaml::load($default_path);
                if (file_exists($local_path)) {
                    $local_data = sfYaml::load($local_path);
                    $config_data = array_replace_recursive($config_data, $local_data);
                }
            }else{
                return $default;
            }

            self::$loaded_configs[$config_name] = $config_data;
        }
        if (!self::$loaded_configs[$config_name]) self::$loaded_configs[$config_name]=array();
        $res = self::getDeepArrayValue(self::$loaded_configs[$config_name], $what);
        return $res !== null ? $res : $default;
    }

    /**
     * Return current project config
     * @static
     * @param mixed $what
     * @param mixed $default
     * @param bool $force_reload
     * @return mixed
     */
    static public function getProjectConfig($what = null, $default = null, $force_reload = false) {
        return self::getConfig('project', $what, $default, $force_reload);
    }

    /**
     * Return current database config
     * @static
     * @param mixed $what
     * @param mixed $default
     * @param bool $force_reload
     * @return mixed
     */
    static public function getDatabaseConfig($what = null, $default = null, $force_reload = false) {
        return self::getConfig('database', $what, $default, $force_reload);
    }

    /**
     * Change config key value. if is_temporary is false - write changes to the file
     *
     * @static
     * @param $config_name
     * @param string $what
     * @param mixed $value
     * @param bool $is_temporary
     */
    static public function setConfig($config_name, $what, $value, $is_temporary = true) {
        if (empty(self::$loaded_configs[$config_name])) self::getConfig($config_name);
        if (!isset(self::$loaded_configs[$config_name]) || !is_array(self::$loaded_configs[$config_name])) {
            self::$loaded_configs[$config_name]= array();
        }
        self::setDeepArrayValue(self::$loaded_configs[$config_name], $value, $what);
        if (!$is_temporary) {
            $default_path = self::$vars['dir']['root'] . 'config/'.$config_name.'.yml';
            file_put_contents($default_path, sfYaml::dump(self::$loaded_configs[$config_name]));
        }
    }

    /**
     * Change to project config
     * @static
     * @param string $what
     * @param mixed $value
     * @param bool $is_temporary
     */
    static public function setProjectConfig($what, $value, $is_temporary = true) {
        self::setConfig('project', $what, $value, $is_temporary);
    }

    /**
     * Change to database config
     * @static
     * @param string $what
     * @param mixed $value
     * @param bool $is_temporary
     */
    static public function setDatabaseConfig($what, $value, $is_temporary = true) {
        self::setConfig('database', $what, $value, $is_temporary);
    }

    /**
     * Very powerful function to get part of array via "/" delimiter. items/2/title
     * @static
     * @param $array
     * @param string $what
     * @return null
     */
    static public function getDeepArrayValue($array, $what = null) {
        if ($what !== null) {
            $what = explode('/', $what);
            foreach($what as $key) {
                if (! isset($array[$key])) return null;

                $array = $array[$key];
            }
        }

        return $array;
    }

    /**
     * Function to set deep array value. for example you can set sub array to some key
     * @static
     * @param $array
     * @param $value
     * @param string $what
     */
    static public function setDeepArrayValue(&$array, $value, $what = null) {
        $set_to = &$array;
        if ($what) {
            $what = explode('/', $what);
            foreach($what as $key) {
                if (is_object($set_to)) {
                    $set_to[$key] = array();
                } elseif (!array_key_exists($key, $set_to)) $set_to[$key] = array();

                $set_to = &$set_to[$key];
                if (!is_object($set_to) && (is_null($set_to))) $set_to = array();
            }
        }
        $set_to = $value;
    }

    static public function unsetDeepArrayValue(&$array, $what = null) {
        $deep = &$array;
        if ($what) {
            $what = explode('/', $what);
            foreach($what as $i => $key) {
                if (!array_key_exists($key, $deep)) break;
                if ($i == count($what) -1) {
                    unset($deep[$key]);
                } else {
                    $deep = &$deep[$key];
                }
            }
        }
    }

    /**
     * Mix array on different levels
     * @static
     * @param array $handle
     * @param array $exnteder
     */
    static public function extendDeepArrayValue(&$handle, $exnteder) {
        foreach($exnteder as $key=>$value) {
            if (!array_key_exists($key, $handle)) {
                $handle[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                self::extendDeepArrayValue($handle[$key], $value);
            } else {
                $handle[$key] = $value;
            }
        }
    }

    /**
     * @static
     * @return bool is current OS windows family
     */
    static public function isWindows(){
        return (substr(PHP_OS,0,3) == 'WIN');
    }

}

// PHP 5.3 compatibility
if(function_exists('lcfirst') === false) {
    function lcfirst($str) {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}