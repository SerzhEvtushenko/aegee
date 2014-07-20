<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 01.12.2009 0:33:17
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Ability to work with files. It can make thumbnails, store native names, etc.
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class FilesAbility extends slModelAbility {

    /**
     * @var array list of aliases for current model
     */
    private $_aliases   = array();

    /**
     * @var string path for store uploaded files
     */
    private $_base_path = null;

    private $_mlt       = array();

    public $_mixed_methods = array(
        'getFilesFolder'    => array(),
        'attachFiles'       => array(),
        'removeFile'        => array(),
    );

    /**
     * Make upload path writable and check for primary key
     * @throws slModelException
     */
    public function setUp() {
        $this->_base_path = SL::getDirUpload() . slInflector::pluralize(strtolower($this->_model->getClassName())) . '/';
        
        slLocator::makeWritable($this->_base_path);
        if (!$this->_pk) {
            throw new slModelException('Files ability can applies only for Models with Primary field specified');
        }
    }

    /**
     * Remove all files for current model
     * @param $objects
     */
    public function unlink(&$objects) {
        foreach($objects as $object) {
            $path = $this->_base_path . $this->_getFolderHash($object[$this->_pk]);
            slLocator::unlinkRecursive($path);
        }
    }

    /**
     * Calculate base path for store uploaded files and load aliases for current model
     */
    public function bootstrap() {
        $this->_base_path = SL::getDirUpload() . slInflector::pluralize(strtolower($this->_model->getClassName())) . '/';
        $this->_aliases = $this->_model->getStructure('abilities/files');
        $this->_mlt = $this->_model->getStructure('abilities/mlt', array());

//        foreach ($this->_aliases as $alias => $params) {
//            $this->publishAction('get'.slInflector::camelCase($alias));
//        }
    }

    /**
     * Return calculated folder hash for current model
     * @param $objects
     * @param $params
     * @return string
     */
    public function getFilesFolder(&$objects, $params){
        $object = array_pop($objects);
        return $this->_base_path . $this->_getFolderHash($object[$this->_pk]) . '/';
    }

    /**
     * Store uploaded files into relative folders after saving so IDs already created
     * @param $changed
     * @param $all
     */
    public function postSave(&$changed, &$all) {
        $objects = array(&$all);
        $this->process($objects, array());
    }

    /**
     * Automatic loading files for current model instance
     * @param $objects
     * @param array $params
     */
    public function postLoad(&$objects, $params = array()) {
        $this->load($objects, $params);
    }

    /**
     * Load files and create aliases in object structure. Also create thumbnails if specified and doesn't exists.
     * @param $objects
     * @param $params
     * @throws slModelException
     */
    public function load(&$objects, $params) {
        $aliases = array();
        if (isset($params[0]) && is_string($params[0])) {
            $tmp = explode(',', $params[0]);
            foreach($tmp as $alias) {
                $info = $this->_aliases[$alias];
                if ($info) {
                    $aliases[$alias] = $info;
                } else {
                    throw new slModelException('No file alias '.$alias.' defined for Model '.$this->_model->getClassName());
                }
            }
        } else {
            $aliases = $this->_aliases;
        }

        foreach($aliases as $alias=>$info) {
            $alias = strtolower($alias);
            $store_source = false;
            if (isset($info['store_source'])) {
                $store_source  = $info['store_source'];
            } elseif (isset($info['sizes'])) {
                $store_source = true;
            }
            $source_folder = $store_source ? '' : '';

            foreach($objects as $key=>$object) {
                $path = $this->getAliasFolder($object, $alias);

                $mask = isset($info['mask']) ? $info['mask'] : '*.*';
                $value = array();
                $files = GLOB($path.$source_folder.$mask);
                if (count($files)) {
                    foreach($files as $file) {
                        $file_name = substr($file, strrpos($file, '/') + 1);
                        $file_ext = substr($file_name, ($ep = strrpos($file_name, '.')) + 1);
                        $file_name = substr($file_name, 0, $ep);

                        $item = slLocator::getFileInfo($file);

                        if (isset($info['sizes'])) {
                            $item['sizes'] = self::makeThumbnails($file, $info, $path, $file_name, $file_ext);
                        }
                        $value[] = $item;
                    }
                }
                if (count($value)) {
                    if (empty($info['multiple'])) $value = $value[0];
                } else {
                    $value = null;
                }
                $objects[$key][$alias] = $value;
            }
        }
    }

    private function getAliasFolder($object, $alias) {
        $path =  $this->_base_path . $this->_getFolderHash($object[$this->_pk]) . '/' . $alias . '/';
        if (isset($this->_mlt['files']) && in_array($alias, $this->_mlt['files'])) {
            $path .= MLT::getActiveLanguage() . '/';
        }
        return $path;
    }

    /**
     * Store files into relative locations for current model. Also create thumbnails if specified.
     * @param $objects
     * @param $params
     * @throws slModelException
     */
    public function process(&$objects, $params) {
        $aliases = array();
        if (isset($params[0]) && is_string($params[0])) {
            $tmp = explode(',', $params[0]);
            foreach($tmp as $alias) {
                $info = $this->_aliases[$alias];
                if ($info) {
                    $aliases[$alias] = $info;
                } else {
                    throw new slModelException('No file alias '.$alias.' defined for Model '.$this->_model->getClassName());
                }
            }
        } else {
            $aliases = $this->_aliases;
        }

        $mlt = $this->_model->getAbilitiesStructure('mlt');
        foreach($aliases as $alias=>$info) {
            $alias = strtolower($alias);
            $store_source = false;
            if (isset($info['store_source'])) {
                $store_source  = $info['store_source'];
            } elseif (isset($info['sizes'])) {
                $store_source = true;
            }
            /**
             * @todo integrate new folder structure for thumbnails
             */

            $source_folder = $store_source ? '' : '';

            if (isset($_FILES[$alias]) && !empty($_FILES[$alias]['name'])) {
                foreach($objects as $object_key=>$object) {
                    $path = $this->getAliasFolder($object, $alias);

                    $mask = isset($info['mask']) ? $info['mask'] : '*.*';

                    $files = self::reformatFilesArray($_FILES[$alias]);
                    slLocator::makeWritable($path.$source_folder);
                    foreach($files as $file) {
                        $original_name = substr($file['name'], 0, strrpos($file['name'], '.'));
                        $original_ext = substr($file['name'], strlen($original_name)+1);
                        $value = array();

                        // remove old files
                        $files_unlink = array();
                        if (empty($info['multiple']) && !($object == 'preview')) {
                            if ($store_source) {
                                $tmp = GLOB($path.$source_folder.$mask);
                                $files_unlink = array_merge($files_unlink, $tmp);
                            }
                            $tmp = GLOB($path.$mask);
                            $files_unlink = array_merge($files_unlink, $tmp);
                        }

                        // @todo do it securely
                        if (isset($_POST['update_file'])) {
                            $del_file = $_POST['update_file'];
                            $del_name = substr($del_file, 0, strrpos($del_file, '.'));
                            $del_ext =  substr($del_file, strlen($del_name));
                            $files_unlink[] = $path.$source_folder.trim($_POST['update_file']);
                        }
                        foreach($files_unlink as $file_unlink) {
                            if (is_file($file_unlink)) unlink($file_unlink);
                        }

                        if (isset($info['extension'])) {
                            $ext = $info['extension'];
                        } else {
                            $ext = $original_ext;
                        }

//                        if (!isset($info['name'])) $info['name'] = 'native';
                        if (isset($info['name'])) {
                            if ($info['name'] == 'native') {
                                $name = $original_name;
                                $ext = $original_ext;
                                while (is_file($path.$source_folder.$name.'.'.$ext)) {
                                    $name .= '_1';
                                }
                            }  else {
                                $name = $info['name'];
                            }
                        } else {
                            $name = md5(time());
                        }

                        // save source

                        if (is_file($file['tmp_name'])) {
//                            echo "ok <br/>" .$file['tmp_name'];
                        } else {
                            throw new slBaseException('g');
                        }
                        copy($file['tmp_name'], $path.$source_folder.$name. '.' .$ext);

                        // create thumbnails if specified
                        if (isset($info['sizes'])) {
                            $value['sizes'] = self::makeThumbnails($path.$source_folder.$name. '.' .$ext, $info, $path, $name, $ext);
                        }

                        $callable = array($this->_model->getClassName(), 'afterImageUpdate');
                        if (is_callable($callable)) {
                            call_user_func($callable, $object['id']);
                        }

                        $value = slLocator::getFileInfo($path.$source_folder.$name. '.' .$ext);
                        // unlink old files
                        @unlink($file['tmp_name']);
                        if (isset($file['after_preview']) && isset($info['sizes'])) $this->removeThumbnails($path.$source_folder.$file['tmp_name'], $info, $path);

                        if (empty($info['multiple'])) {
                            if (!isset($objects[$object_key][$alias])) $objects[$object_key][$alias] = array();

                            $objects[$object_key][$alias][] = $value;
                        } else {
                            $objects[$object_key][$alias] = $value;
                        }
                    }
                }

            }
        }
        
    }

    public function removeFile(&$objects, $params) {
        $this->requireModeSingle($objects);

        $alias = $params[0];

        $alias_params = $this->_model->getStructure('abilities/files/'.$alias);

        if (!$alias_params) {
            return false;
        }

        $files = $params[1];
        if (!is_array($files)) $files = array($files);
        $object = array_pop($objects);

        if (empty($alias_params['multiple'])) {
            $object_files = array($object->$alias);
        } else {
            $object_files = $object->$alias;
        }

        $result = false;

        foreach($object_files as $file) {
            if (in_array($file['link'], $files)) {
                @unlink($file['path']);
                if (isset($file['sizes'])) {
                    foreach($file['sizes'] as $size_info) {
                        @unlink($size_info['path']);
                    }
                }
                $result = true;
            }
        }
        return $result;
    }


    /**
     * Attach file to your object,
     * 1st param - alias or aliases array
     * 2nd param - array with standart file info
     * @param $objects
     * @param $params
     */
    public function attachFiles(&$objects, $params) {
        $this->requireModeSingle($objects);
        $object = array_pop($objects);

        $aliases = array();
        $resource = $params[1];

        $tmp = explode(',', $params[0]);
        foreach($tmp as $alias) {
            $info = $this->_aliases[$alias];
            if ($info) {
                $aliases[$alias] = $info;
            } else {
                throw new slModelException('No file alias '.$alias.' defined for Model '.$this->_model->getClassName());
            }
        }

        foreach($aliases as $alias=>$info) {
            $store_source = false;
            if (isset($info['store_source'])) {
                $store_source  = $info['store_source'];
            } elseif (isset($info['sizes'])) {
                $store_source = true;
            }
            $source_folder = $store_source ? '' : '';
            if (isset($resource[$alias]) && !empty($resource[$alias]['name'])) {
                $path = $this->getAliasFolder($object, $alias);
                $mask = isset($info['mask']) ? $info['mask'] : '*.*';

                $files = self::reformatFilesArray($resource[$alias]);

                slLocator::makeWritable($path.$source_folder);
                foreach($files as $file) {
                    $original_name = substr($file['name'], 0, strrpos($file['name'], '.'));
                    $original_ext = substr($file['name'], strlen($original_name)+1);
                    $value = array();

                    // remove old files
                    $files_unlink = array();
                    if (empty($info['multiple']) && !($object == 'preview')) {
                        if ($store_source) {
                            $tmp = GLOB($path.$source_folder.$mask);
                            $files_unlink = array_merge($files_unlink, $tmp);
                        }
                        $tmp = GLOB($path.$mask);
                        $files_unlink = array_merge($files_unlink, $tmp);
                    }

                    // @todo do it securely
                    if (isset($_POST['update_file'])) {
                        $del_file = $_POST['update_file'];
                        $del_name = substr($del_file, 0, strrpos($del_file, '.'));
                        $del_ext =  substr($del_file, strlen($del_name));
                        $files_unlink[] = $path.$source_folder.trim($_POST['update_file']);
                    }
                    foreach($files_unlink as $file_unlink) {
                        @unlink($file_unlink);
                    }

                    if (isset($info['extension'])) {
                        $ext = $info['extension'];
                    } else {
                        $ext = $original_ext;
                    }

                    if (isset($info['name'])) {
                        if ($info['name'] == 'native') {
                            $name = $original_name;
                            $ext = $original_ext;
                            while (is_file($path.$source_folder.$name.'.'.$ext)) {
                                $name .= '_1';
                            }
                        }  else {
                            $name = $info['name'];
                        }
                    } else {
                        $name = md5(time());
                    }

                    // save source
                    copy($file['tmp_name'], $path.$source_folder.$name. '.' .$ext);
                    // create thumbnails if specified
                    if (isset($info['sizes'])) {
                        $value['sizes'] = self::makeThumbnails($path.$source_folder.$name. '.' .$ext, $info, $path, $name, $ext);
                    }

                    $value = slLocator::getFileInfo($path.$source_folder.$name. '.' .$ext);
                    // unlink old files
                    @unlink($file['tmp_name']);
                    if (isset($file['after_preview']) && isset($info['sizes'])) $this->removeThumbnails($path.$source_folder.$file['tmp_name'], $info, $path);

                    $object[$alias] = $value;
                }

            }

        }

        return $object;
    }

    static public function reformatFilesArray($files_array) {
        if (is_array($files_array['name'])) {
            $files = array();
            foreach($files_array['name'] as $key=>$name) {
                $files[] = array(
                    'name'	=> $name,
                    'type'	=> $files_array['type'][$key],
                    'tmp_name'	=> $files_array['tmp_name'][$key],
                    'error'	=> $files_array['error'][$key],
                    'size'	=> $files_array['size'][$key],
                );
            }
        } else {
            $files = array($files_array);
        }
        return $files;

    }

    static public function removeGlobalFilesItem($tmp_name, $_files_array) {
        foreach($_files_array['name'] as $key=>$value) {
            if ($_files_array['tmp_name'][$key] == $tmp_name) {
                unset($_files_array['name'][$key]);
                unset($_files_array['type'][$key]);
                unset($_files_array['tmp_name'][$key]);
                unset($_files_array['error'][$key]);
                unset($_files_array['size'][$key]);
            }
        }
    }

    /**
     * Create thumbnails
     * @param string $file
     * @param array $info
     * @param string $path
     * @param string $name
     * @param string $ext
     * @return array result as pathes
     */
    static public function makeThumbnails($file, $info, $path, $name, $ext) {
		$res = array();
        $default_process = !empty($info['process']) ? $info['process'] : 'fitIn';
		foreach($info['sizes'] as $alias=>$info) {
            $process = $default_process;
            if (is_array($info)) {
                if (isset($info['process'])) $process = $info['process'];
                $info = $info['size'];
            }
            $to = $path. $alias . '/' . $name. '.' .$ext;
            if (!is_file($to)) {
                if (!is_dir($path. $alias)) mkdir($path. $alias, 0777);
                call_user_func(array('slImage', $process), $file, $to, $info);
            }
            $res[$alias] = slLocator::getFileInfo($to);
		}
		return $res;
	}

    /**
     * Removing all related thumbnails for current model
     * @param string $file
     * @param array $info
     * @param string $path
     * @return bool
     */
    private function removeThumbnails($file, $info, $path) {
		$res = false;

		$file_name = substr($file, strrpos($file, '/') + 1);
		$file_ext = substr($file_name, ($ep = strrpos($file_name, '.')) + 1);
		$file_name = substr($file_name, 0, $ep);

		foreach($info['dimensions'] as $alias=>$dimension) {
			$to = $path.$file_name. '_' .$alias. '.' .$file_ext;
			if (is_file($to)) {
				@unlink($to);
				$res = true;
			}
		}
		return $res;
    }

    /**
     * Calculate hash folder name for current model
     * @param string $value
     * @return string
     */
    private function _getFolderHash($value) {
        if (!intval($value[0])) {
            $int = ord($value[0]) % 10;
        } else {
            $int = $value[0] % 10;
        }
        return $int . '/' . $value;
    }

}
