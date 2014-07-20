<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 24.11.2009 9:09:16
 */

/**
 * Working with files and directories. Can search, make writable, recursively delete, etc
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slLocator {

    const
        TYPE_ALL            = 0,
        TYPE_DIR            = 1,
        TYPE_FILE           = 2,

        HYDRATE_FULL        = 0,
        HYDRATE_NAMES       = 1,
        HYDRATE_NAMES_PATH  = 2;

    static private
        $instance       = null;


    private
        $paths          = array(),
        $_base_path      = '';

    /**
     * Return instance of slLocator
     *
     * @static
     * @return slLocator
     */
    static public function getInstance() {
        if (!self::$instance) {
            self::$instance = new slLocator();
        }

        return self::$instance;
    }

    static public function setBasePath($path = '') {
        self::$instance->_base_path = $path;
    }

    public function in($where, $recursive = false) {
        if ($this->_base_path && (strpos($where, $this->_base_path) !== false)) {
            $where = substr($where, strlen($this->_base_path));
        }
        $this->paths[] = $where;
        if ($recursive) {
            $dirs = GLOB($this->_base_path . $where . '/*', GLOB_ONLYDIR);
            foreach($dirs as $dir) {
                $this->in($dir, true);
            }
        }
        return $this;
    }

    public function resetPaths() {
        $this->paths = array();
    }

    public function find($what= '{,.}*', $type = slLocator::TYPE_ALL, $hydrate = slLocator::HYDRATE_FULL) {
        $res = array();
        foreach($this->paths as $path) {
            if ($this->_base_path && (strpos($path, $this->_base_path) !== false)) {
                $path = str_replace($this->_base_path, '', $path);
            }
            $files = GLOB($this->_base_path . $path . '/' . $what, $type == slLocator::TYPE_DIR ? GLOB_ONLYDIR : GLOB_BRACE);
            $res = array_merge($res, $files);

        }
        foreach($res as $key=>$field) {
            $name = substr($field, strrpos($field, '/')+1);
            if ($name == '.' || $name == '..') {
                unset($res[$key]);
                continue;
            }
            if ($type == slLocator::TYPE_FILE) {
                if (is_dir($field)) unset($res[$key]);
            }
            if ($hydrate == slLocator::HYDRATE_NAMES) {
                $res[$key] = $name;
            } elseif($hydrate == slLocator::HYDRATE_NAMES_PATH) {
                $res[substr($field, strrpos($field, '/')+1)] = $field;
                unset($res[$key]);
            }
        }
        $this->resetPaths();
        return $res;
    }

    static public function copyRecursive($path_from, $path_to, $mask = '{,.}*', $skip='.svn') {
        if (is_file($path_from)) {
            copy($path_from, $path_to);
            return true;
        }
        self::makeWritable($path_to);

        $files = GLOB($path_from . $mask, GLOB_BRACE);
        foreach($files as $file) {
            $name = substr($file, strrpos($file, '/')+1);
            if ($name == '.' || $name == '..' || $name == $skip) continue;
            if (is_dir($file)) {
                self::makeWritable($path_to . $name);
                self::copyRecursive($file . '/' . $mask, $path_to . $name . '/');
            } else {
                copy($file, $path_to . $name);
            }
        }
    }

    static public function makeWritable($paths, $autocreate = true) {
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        foreach($paths as $path) {
            if (!is_dir($path)) {
                if ($autocreate) {
                    mkdir($path, 0777, true);
                    chmod($path, 0777);
                } else {
                    throw new Exception('Path '.$path.' is not exists!');
                }
            }
            if (!is_writable($path)) {
                chmod($path, 0777);
            }
            if (!is_writable($path)) {
                throw new Exception('Path '.$path.' could not make writtable!');
            }
        }
    }

    static public function unlinkRecursive($path) {
        if (!file_exists($path)) return true;
        if (!is_dir($path) || is_link($path)) return unlink($path);

        foreach (scandir($path) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!self::unlinkRecursive($path . "/" . $item)) {
                chmod($path . "/" . $item, 0777);
                if (!self::unlinkRecursive($path . "/" . $item)) return false;
            };
        }
        return rmdir($path);
    }

    static 	public function getFileInfo($path) {
        if (!is_file($path)) return array(
            'is_exists' => false
        );
        
        $last_slash = strrpos($path, '/');
        $folder     = '';
        $file       = $path;

        if ($last_slash !== false) {
            $folder = substr($path, 0, $last_slash);
            $file = substr($path, $last_slash+1);
        }
        $ext_pos    = strrpos($file, '.');
        $file_name  = $file;
        $ext        = '';
        if ($ext_pos !== false) {
            $file_name  = substr($file, 0, $ext_pos);
            $ext        = substr($file, $ext_pos);
        }

        $mess = array('b', 'Kb', 'Mb', 'Gb', 'Tb');
        $i = 0;
		$link_folder = substr($folder, mb_strlen(SL::getDirWeb())) . '/';
		$value = array(
			'size'      => @filesize(realpath($path)),
			'is_exists' => true,
			'link'      => $link_folder.$file_name.$ext,
			'path'      => $path,
			'full_name' => $file_name.$ext,
			'name'      => $file_name,
			'ext'       => $ext
		);
		while(($i < count($mess) - 1) && ($value['size'] > 1024)) {
			$i++;
			$value['size'] = $value['size'] / 1024;
		}
		$value['size'] = ceil($value['size']).' '.$mess[$i];
		return $value;
	}

    static public function intToSize($int) {
        $mess = array('b', 'Kb', 'Mb', 'Gb', 'Tb');
        $i = 0;
        while(($i < count($mess) - 1) && ($int > 1024)) {
            $i++;
            $int = $int / 1024;
        }
        return ceil($int).' '.$mess[$i];
    }

     static public function sendFile($file_path, $file_name) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Type: application/octet-stream;", true);
        header("Content-Disposition: attachment; filename=\"" . $file_name . "\";");
        header("Content-Transfer-Encoding:  binary");
        header("Content-Length: ".filesize($file_path));
        readfile($file_path);
        SL::setProjectConfig('dev_mode', false);
        die();
    }

}


