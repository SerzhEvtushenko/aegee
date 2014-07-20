<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 20.11.2009 10:22:16
 */

/**
 * Autoload all classes for engine
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slAutoloader {

	static protected    $registered = false;
	static protected    $instance   = null;

	protected           $dirs       = array();
	protected           $files      = array();
	protected           $classes    = array();

    /**
     * @return slAutoloader Instance of slAutoloader
     */
	static public function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new slAutoloader();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->addDefaults();
	}

	/**
	 * Register autoloader
	 */
	static public function register() {
		ini_set('unserialize_callback_func', 'spl_autoload_call');
		if (false === spl_autoload_register(array(self::getInstance(), 'autoload'))) {
		  throw new Exception(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
		}

		self::$registered = true;
	}

    static public function getRegistered() {
        return self::getInstance()->classes;
    }

    /**
     * Unregistr autoloader
     * @static
     */
	static public function unregister() {
		spl_autoload_unregister(array(self::getInstance(), 'autoload'));
		self::$registered = false;
	}


	/**
	 * Autoload classes
	 *
	 * @param String $class
	 * @return bool
	 */
	public function autoload($class) {
		// class already exists
		if (class_exists($class, false) || interface_exists($class, false))	{
		    return true;
		}
		if (isset($this->classes[$class])) {
            try {
                require $this->classes[$class];
            }  catch (Exception $e) {
                $e->printStackTrace();
            }
            return true;
        }
        return false;
	}

    /**
     * Add folder to scan
     * @param $dir_name
     * @param bool $recursive
     */
	public function addDir($dir_name, $recursive = false, $cache_it = false) {
	    if (!in_array($dir_name, $this->dirs)) {
            $this->dirs[] = $dir_name;
        }

        $need_to_process    = true;
        $need_to_cache      = false;
        $cached_file        = $dir_name . '/.autoload';
        if ($cache_it) {
            if (is_file($cached_file) && (filectime($cached_file) > filectime(__FILE__))) {
                $need_to_process = false;
                $data = unserialize(file_get_contents($cached_file));
                $this->dirs = array_merge($this->dirs, $data['dirs']);
                $this->classes = array_merge($this->classes, $data['classes']);
            } else {
                $need_to_cache = true;
            }
        }

        if ($need_to_process) {
            $files = GLOB($dir_name . '/*', GLOB_NOSORT);
	        foreach($files as $file) {
                if (is_dir($file) && $recursive) {
                    $this->addDir($file, true);
                } else {
                    if (strpos($file, '.class.php') !== false) {
                        $class_name = substr($file, strrpos($file, '/')+1, -10);
                        $this->classes[$class_name] = $file;
                    }
                }
            }
        }
        if ($need_to_cache) {
            file_put_contents($cached_file, serialize(array('classes'=>$this->classes, 'dirs'=>$this->dirs)));
            @chmod($cached_file, 0777);
	    }
	}

	/**
	 * Adding defaults path and classes to search stack
	 */
	private function addDefaults() {
		$this->addDir(realpath(dirname(__FILE__) . '/../'), true, true);
	}

}
?>
