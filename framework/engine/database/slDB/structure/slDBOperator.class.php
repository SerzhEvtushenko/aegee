<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 25.11.2009 16:59:36
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Operate with Database and models
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slDBOperator {

    /**
     * @var array info about tables
     */
    private $_tables        = null;

    /**
     * @var string path for slDB folder
     */
    private $_storage_path  = null;

    /**
     * @var string path for models
     */
    private $_models_path   = null;

    /**
     * @var string path for YML structures
     */
    private $_structures_path = null;

    /**
     * @var array all loaded structures
     */
    private $_structure     = array();

    /**
     * @var slDBOperator current instance
     */
    static private $_instance       = null;

    /**
     * @var array used for check syntax in model structure files
     */
    static private $_allowed_structure_keys = array(
        'table',
        'columns',
        'indexes',
        'constraints',
        'relations',
        'abilities',
        'custom',
    );

    /**
     * Fills local variables with correct values
     */
    public function __construct() {
        $this->_storage_path    = SL::getDirUserLibs() . 'slDB/';
        $this->_models_path     = $this->_storage_path . 'classes/';
        $this->_structures_path = $this->_storage_path . 'structure/';
        $this->loadStructureFiles($this->_structures_path);
    }

    /**
     * Change store path
     * @param $path
     */
    public function setStoragePath($path) {
        slLocator::makeWritable($path);
        $this->_storage_path = $path;
        $this->_structures_path = $this->_storage_path . 'structure/';
    }

    /**
     * Change models path
     * @param $path
     */
    public function setModelsPath($path) {
        slLocator::makeWritable($path);
        $this->_models_path = $path;
    }

    /**
     * Return current instance
     * @static
     * @param bool $reload_structure
     * @return slDBOperator
     */
    static public function getInstance($reload_structure = false) {
        if (!self::$_instance) {
            self::$_instance = new slDBOperator();
            $reload_structure = false;
        }
        if ($reload_structure) self::$_instance->loadStructureFiles(self::$_instance->_structures_path);
        return self::$_instance;
    }

    /**
     * Using for creating database
     * @param string $DBName
     * @param string $charset
     * @param string $collation
     * @return bool
     */
    public function createDB($DBName = null, $charset = 'utf8', $collation = 'utf8_general_ci') {
        Q::execSQL('CREATE DATABASE `'.$DBName.'` CHARACTER SET '.$charset.' COLLATE '.$collation);
        return true;
    }

    /**
     * Drop specified Database
     * @param null $DBName
     * @return mixed
     */
    public function dropDB($DBName = null) {
        return Q::execSQL('DROP DATABASE '.$DBName);
    }

    /**
     * Return list of tables from connected database
     * @param bool $force_fetch reload tables list
     * @return array|null
     */
    public function getDBTables($force_fetch = false) {
        if ($this->_tables && !$force_fetch) return $this->_tables;

        $res = Q::execSQL('SHOW TABLES');
        $this->_tables = array();
        if ($res->rowCount()) {
            $res = $res->fetchAll(PDO::FETCH_NUM);
            foreach($res as $info) {
                $this->_tables[$info[0]] = array('name'=>$info[0]);
            }
        }
        return $this->_tables;
    }

    /**
     * Get Full information about table structure and return it as array with keys
     * @param string $table_name
     * @return array structure
     */
    public function getTableStructure($table_name) {

        if (empty($this->_tables[$table_name])) {
            try {
                $res = Q::execSQL('SHOW CREATE TABLE `'.$table_name.'`');
            } catch (Exception $e) {
                return null;
            }

        } else {
            return $this->_tables[$table_name];
        }

        $this->_tables[$table_name] = array();
        if ($res->rowCount()) {
            $res = $res->fetchAll(PDO::FETCH_NUM);
            foreach($res as $row) {

                $this->_tables[$table_name] = array(
                    'table'			=>$table_name,
                    'columns'		=>array(),
                    'indexes'		=>array(),
                    'constraints'	=>array(),
                    'character_set'	=>'utf8',
                    'collation'		=>'utf8_general_ci'
                );

                $structure = $row[1];
                $info = explode("\n", $structure);
                array_shift($info);

                while($row = trim(array_shift($info))) {
                    if ($row[strlen($row)-1] == ',') $row = substr($row, 0, -1);
                    
                    if ($row[0] == '`') {
                        $column = array();
                        $pos = strpos($row, '`', 1);
                        $column['name'] = substr($row, 1, $pos-1);
                        $row = trim(substr($row, $pos+1));

                        $pos = strpos($row, ' ');
                        if ($pos === false) $pos = strlen($row);
                        $column['type'] = substr($row, 0, $pos);
                        if (strpos($row, 'unsigned') !== false) {
                            $column['type'] .= ' unsigned';
                        }
                        $row = trim(substr($row, $pos + (isset($column['unsigned']) ? 9 : 0)));

                        $column['not_null'] = false;

                        if (strpos(strtolower($row), 'auto_increment')) {
                            $column['auto_increment'] = true;
                            $column['not_null'] = true;
                        } else {
                            if (($pos = strpos(strtolower($row), 'zerofill')) !== false) {
                                $column['zerofill'] = true;
                                $row = trim(substr($row, $pos + 8));
                            }

                            if (($pos = strpos($row, 'NULL')) !== false) {
                                $null = substr($row, 0, $pos + 4);
                                $row = trim(substr($row, $pos + 4));
                                if (strpos(strtolower($null), 'not') !== false) {
                                    $column['not_null'] = true;
                                }
                            }
                            if (strpos(strtolower($row), 'default') !== false) {
                                $row = trim(substr($row, 8));
                                $row = str_replace(array('"', '\''), '', $row);
                                $pos = strpos($row, ' ');
                                if ($pos === false) $pos = strlen($row);
                                $column['default'] = substr($row, $pos+1);
                                if ($column['default'] === false) $column['default'] = '';
                            }
                        }
                        $this->_tables[$table_name]['columns'][$column['name']] = $column;
                    } else if (($pos = strpos($row, 'KEY')) !== false) {
                        $key_start = strpos($row, '`')+1;

                        if (strpos($row, 'CONSTRAINT') !== false) {
                            $name = substr($row, $key_start, strpos($row, '`', $key_start+1)-$key_start);
                            $con = array('name' => $name);

                            $pos = strpos($row, 'FOREIGN KEY')+14;
                            $con['foreign_key'] = substr($row, $pos, strpos($row, '`', $pos) - $pos);

                            $pos = strpos($row, 'REFERENCES')+12;
                            $con['references'] = substr($row, $pos, strpos($row, '`', $pos) - $pos);

                            $pos = strpos($row, '(', $pos) + 2;
                            $con['local_key'] = substr($row, $pos, strpos($row, '`', $pos) - $pos);

                            if (($pos = strpos($row, 'ON DELETE'))) {
                                $pos += 10;
                                $con['on_delete'] = strtolower(substr($row, $pos, strpos($row, ' ON', $pos) - $pos));
                            } else {
                                $con['on_delete'] = 'restrict';
                            }

                            if (($pos = strpos($row, 'ON UPDATE'))) {
                                $pos += 10;
                                $con['on_update'] = strtolower(substr($row, $pos));
                            } else {
                                $con['on_update'] = 'restrict';
                            }

//							vd('constraint', $row, '!@#');

                            $this->_tables[$table_name]['constraints'][$name] = $con;
                        } elseif (strpos($row, 'PRIMARY') !== false) {
                            $key = substr($row, $key_start, strpos($row, '`', $key_start+1)-$key_start);
                            $this->_tables[$table_name]['indexes']['primary'] = array('columns'=>explode(',', $key), 'type'=>'primary');
                        } else {
                            $name = substr($row, $key_start, strpos($row, '`', $key_start+1)-$key_start);
                            $pos = strpos($row, '(')+1;
                            $key = str_replace('`', '', substr($row, $pos, strpos($row, ')') - $pos));
                            $key = explode(',', $key);
                            $item = array('type' => (strpos($row, 'UNIQUE') !== false) ? 'unique' : 'simple', 'columns'=>$key);
                            $this->_tables[$table_name]['indexes'][$name] = $item;
                        }
                    }
                }
                return $this->_tables[$table_name];
            }
        }
    }


    /**
     * Find differences between table structure in DB and $var_info structure
     * @param array $var_structure array with structure for diff
     * @param string $table_name specified table if not specified in structure
     * @return array table_exists true|false and sql array for altering
     */
    public function getDifferenceSQL($var_structure, $table_name = null) {
        if (!$table_name) $table_name = $var_structure['table'];

        $current_structure = $this->getTableStructure($table_name);
        $res = array('result'=>false, 'sql'=>array());
        if (!$current_structure) {
            $sql = $this->generateTableSQL($var_structure);
            $res['result'] = true;
            $res['sql']['ADD'][] = $sql;
            return $res;
        }

        // synchronizing columns
        foreach($var_structure['columns'] as $name=>$info) {
            if (!empty($var_structure['abilities']['mlt']['columns']) && in_array($name, $var_structure['abilities']['mlt']['columns'])) continue;
            if (!is_array($info)) $info = array('type'=>$info);
            if (!isset($info['name'])) $info['name'] = $name;

            if (!isset($current_structure['columns'][$name]) && !(isset($info['old_name']) && isset($current_structure['columns'][$info['old_name']]))) {
                if ((isset($info['auto_increment']))) {
                    unset($info['auto_increment']);
                    $res['sql']['ADD'][] = 'ALTER TABLE '.$var_structure['table'].' ADD COLUMN '.$this->generateColumnSQL($info);
                    if (!isset($current_structure['indexes']['primary'])) {
                         $var_structure['indexes']['primary'] = array('columns'=>$name);
                         $current_structure['indexes']['primary'] = array('columns'=>$name);
                         $res['sql']['ADD'][] = 'ALTER TABLE '.$var_structure['table'].' ADD PRIMARY KEY (`'.$name.'`)';
                     }
                     $info['auto_increment'] = true;
                     $res['sql']['CHANGE'][] = 'ALTER TABLE '.$var_structure['table'].' MODIFY COLUMN '.$this->generateColumnSQL($info);
                } else {
                    $res['sql']['ADD'][]  = 'ALTER TABLE '.$var_structure['table'].' ADD COLUMN '.$this->generateColumnSQL($info);
                }

            } else {
                if (isset($info['old_name'])) {
                    $current_structure['columns'][$name] = $current_structure['columns'][$info['old_name']];
                    unset($current_structure['columns'][$info['old_name']]);
                }
                $db_info = $current_structure['columns'][$name];
                if (
                    (isset($info['default']) && (strpos($info['default'], '#') === false) && (!isset($db_info['default']) || ($info['default'] != $db_info['default'])))
                    || (!isset($info['default']) && !empty($db_info['default']))
                    || ($info['type'] != $db_info['type'])
                    || isset($info['old_name'])
                    || (isset($info['auto_increment']) && !(isset($db_info['auto_increment'])))
                    || (!isset($info['auto_increment']) && isset($info['not_null']) && ($db_info['not_null'] != $info['not_null']))
                    || (!isset($info['auto_increment']) && !isset($info['not_null']) && $db_info['not_null'])
                ) {
                    if ((isset($info['auto_increment']) && !(isset($db_info['auto_increment'])))) {
                        if (!isset($current_structure['indexes']['primary'])) {
                            $var_structure['indexes']['primary'] = array('columns'=>$name);
                            $current_structure['indexes']['primary'] = array('columns'=>$name);
                            $res['sql']['ADD'][] = 'ALTER TABLE '.$var_structure['table'].' ADD PRIMARY KEY (`'.$name.'`)';
                        }
                    }
                    $res['sql']['CHANGE'][] = 'ALTER TABLE '.$var_structure['table'].' CHANGE COLUMN '.(isset($info['old_name']) ? '' : '`'.$name.'` ').$this->generateColumnSQL($info);
                }
            }
        }
        // Dropping unused columns from DB
        foreach($current_structure['columns'] as $name=>$info) {
            if (!isset($var_structure['columns'][$name])) {
                $sql_diff = 'ALTER TABLE '.$var_structure['table'].' DROP COLUMN `'.$name.'`';
                $res['sql']['DROP'][] = $sql_diff;
            }
        }

        if (isset($var_structure['indexes'])) {
            // synchronizing indexes
            foreach($var_structure['indexes'] as $name=>$info) {
                $sql_diff = 'ALTER TABLE '.$var_structure['table'];

                if (!isset($current_structure['indexes'][$name])) {
                    if (!isset($info['name'])) $info['name'] = $name;
                    $sql_diff .= ' ADD '.$this->generateIndexSQL($info);
                    $res['sql']['ADD'][] = $sql_diff;;
                } else {
                    //@todo Altering indexes
                }
            }
        }

        // Dropping unused indexes from DB
        foreach($current_structure['indexes'] as $name=>$info) {
            if (!isset($var_structure['indexes'][$name])) {
                $sql_diff = 'ALTER TABLE '.$var_structure['table'].($name == 'primary' ? ' DROP PRIMARY KEY' : ' DROP KEY `'.$name.'`');
                $res['sql']['DROP'][] = $sql_diff;
            }
        }
        if (isset($var_structure['constraints'])) {
            // synchronizing constraints
            foreach($var_structure['constraints'] as $name=>$info) {
                $sql_diff = 'ALTER TABLE '.$var_structure['table'];
                if (!isset($current_structure['constraints'][$name])) {
                    $sql_diff .= ' ADD '.$this->generateConstraintSQL($info);
                    $res['sql']['ADD'][] = $sql_diff;;
                } else {
                    //@todo Altering indexes
                }
            }
        }


        if (count($res['sql'])) {
            $res['result'] = true;
        }
        return $res;
    }

    /**
     * Update database from array
     * @param string|array $model_names
     * @param bool $safe_update if true - method will delete not specified fields
     * @throws slDBException
     */
    public function updateDBFromStructure($model_names = null, $safe_update = true) {
        if (!empty($model_names)) {
            if (!is_array($model_names)) $model_names = array($model_names);
        } else {
            $model_names = array_keys($this->_structure);
        }

        foreach($model_names as $model_name) {
            if ($model_names && empty($this->_structure[$model_name])) {
                throw new slDBException('There is no structure defined for '.$model_name);
            }
            $diffs = $this->getDifferenceSQL($this->_structure[$model_name]);
            if ($diffs['result'] === true) {
                Q::execSQL('SET FOREIGN_KEY_CHECKS = 0');
                if (!empty($diffs['sql']['ADD'])) Q::execSQL($diffs['sql']['ADD']);
                if (!empty($diffs['sql']['CHANGE'])) {
                    Q::execSQL($diffs['sql']['CHANGE']);
                    foreach($this->_structure[$model_name]['columns'] as $name=>$info) {
                        if (isset($info['old_name'])) {
                            unset($this->_structure[$model_name]['columns'][$name]['old_name']);
                        }
                    }
                    $this->saveYamlStructure($model_name, $this->_structure[$model_name]);
                }

                if (!empty($diffs['sql']['DROP']) && !$safe_update) Q::execSQL($diffs['sql']['DROP']);
                if (isset($this->_tables[$this->_structure[$model_name]['table']])) unset($this->_tables[$this->_structure[$model_name]['table']]);
            }
        }
    }

    /**
     * Updates relations in database
     * @param null $model_names
     * @param bool $safe_update
     * @throws slDBException
     */
    public function updateDBRelations($model_names = null, $safe_update = true) {
        if (!empty($model_names)) {
            if (!is_array($model_names)) $model_names = array($model_names);
        } else {
            $model_names = array_keys($this->_structure);
        }

        foreach($model_names as $model_name) {
            if ($model_names && empty($this->_structure[$model_name])) {
                throw new slDBException('There is no structure defined for '.$model_name);
            }
            if (empty($this->_structure[$model_name]['relations'])) continue;

            foreach($this->_structure[$model_name]['relations'] as $r_name=>$r) {
                $model_object = new $model_name;
                $info = self::calculateRelationVariables($model_object, $r, $r_name);
                unset($model_object);
                switch($info['type']) {
                    case 'many_to_many':
                        $this->updateManyTable($info);
                        break;
                    case 'many_to_one':
                        break;
                    case 'one_to_many':
                        break;
                }
            }
        }
    }

    /**
     * Updates many to many tables
     * @param $info
     * @return mixed
     */
    public function updateManyTable($info) {
        $structure = array(
            'table'     => $info['many_table'],
            'columns'   => array(),
            'indexes'   => array(),
            'constraints'   => array(),
        );


        $local_name = slInflector::singularize($info['local_table']);
        $foreign_name = slInflector::singularize($info['foreign_table']);

        $structure['columns']['id'] = array('type' => 'int(11) unsigned', 'auto_increment'=>true);
        $structure['columns']['id_'. $local_name] = 'int(11) unsigned';
        $structure['columns']['id_'. $foreign_name] = 'int(11) unsigned';
        $structure['indexes']['primary'] = array('columns'=>array('id'), 'type'=>'primary');
        $structure['indexes']['unique_id_'.$local_name.'_id_'.$foreign_name] = array(
            'columns'   => array(
                'id_'.$local_name,
                'id_'.$foreign_name
            ),
            'type'      => 'unique'
        );
        $fk_info = array(
            'local_table'   => $info['many_table'],
            'foreign_table' => $info['local_table'],
            'local_field'   => 'id_'.$local_name,
            'foreign_field' => $info['local_key']
        );
        $structure['constraints'][$this->generateForeignKeyName($fk_info)] = $fk_info;
        $fk_info = array(
            'local_table'   => $info['many_table'],
            'foreign_table' => $info['foreign_table'],
            'local_field'   => 'id_'.$foreign_name,
            'foreign_field' => $info['foreign_key']
        );
        $structure['constraints'][$this->generateForeignKeyName($fk_info)] = $fk_info;

        $diffs = $this->getDifferenceSQL($structure, $info['many_table']);
        if ($diffs['result'] === true) {
            Q::execSQL('SET FOREIGN_KEY_CHECKS = 0');
            if (!empty($diffs['sql']['ADD'])) Q::execSQL($diffs['sql']['ADD']);
        }
        return $diffs['result'];

    }

    /**
     * Loading structure from YAML files
     * First loading "structure.yml" if it exists
     *
     * @param string $path Path where yaml are stored
     * @return void
     */
    public function loadStructureFiles($path) {
        $files = slLocator::getInstance()->in($path)->find('*.yml', slLocator::TYPE_FILE,slLocator::HYDRATE_NAMES_PATH);
        $data = array();

        if (isset($files['structure.yml'])) {
            $data = sfYaml::load($files['structure.yml']);
            unset($files['structure.yml']);
        }

        foreach($files as $file) {
            $model_name = substr($file, strrpos($file, '/')+1, -4);
            $data = array_merge($data, array(ucfirst($model_name) => sfYaml::load($file)));
        }

        $data = self::fixYamlStructures($data);
        $this->_structure = $data;
    }

    /**
     * Return array with structure for specified model
     * @param string $model_name
     * @return mixed
     * @throws slDBException
     */
    public function getYamlStructure($model_name) {
        if (empty($this->_structure)) {
            $this->loadStructureFiles($this->_structures_path);
        }

        $model_name = ucfirst($model_name);
        if (empty($this->_structure[$model_name])) {
            throw new slDBException('There is no structure defined for '.$model_name);
        }
        return $this->_structure[$model_name];

    }

    /**
     * Save structure to relative YML file
     *
     * @param string $model_name
     * @param array $structure
     * @param boolean $is_separate if specified false write to "structure.yml"
     * @return boolean
     */
    public function saveYamlStructure($model_name, $structure, $is_separate = true) {
        
        $file_name  = strtolower($model_name);
        $model_name = ucfirst($model_name); 
        if (is_file($this->_storage_path. 'structure/'. $file_name . '.yml' )) {
            unlink($this->_storage_path. 'structure/'. $file_name . '.yml');
        }

        $full_structure = array();
        if (is_file($this->_storage_path. 'structure/structure.yml')) {
            $full_structure = sfYaml::load($this->_storage_path. 'structure/structure.yml');
            foreach($full_structure as $key=>$item) {
                unset($full_structure[$key]);
                $full_structure[ucfirst($key)] = $item;
            }
        }
        $structure = self::fixYamlStructures($structure, true);
        $is_separate = SL::getDatabaseConfig('separate_yaml', true);
        if ($is_separate) {
            if (isset($full_structure[$model_name])) {
                unset($full_structure[$model_name]);
            }
            if (!empty($full_structure)) {
                file_put_contents($this->_storage_path. 'structure/structure.yml', sfYaml::dump($full_structure));
            } else {
                if (is_file($this->_storage_path. 'structure/structure.yml')) @unlink($this->_storage_path. 'structure/structure.yml');
            }
            file_put_contents($this->_storage_path. 'structure/'. $model_name .'.yml', sfYaml::dump($structure));

        } else {
            $full_structure[$model_name] = $structure;
            file_put_contents($this->_storage_path. 'structure/structure.yml', sfYaml::dump($full_structure));
        }
        $this->_structure[$model_name] = $structure;
        return true;
    }

    /**
     * Generate file with BaseClass and Child Class if it's not extists
     * @param string|array $classes_names
     * @param bool $rebuild_abilities
     * @return void
     * @throws slDBException if no structure defined
     */
    public function generateModelClass($classes_names = null, $rebuild_abilities = true) {
        if (is_null($classes_names)) {
            $classes_names = array_keys($this->_structure);
        } elseif (is_string($classes_names)) {
            $classes_names = array($classes_names);
        }

        slLocator::makeWritable($this->_storage_path . 'bases/');
        foreach($classes_names as $class_name) {

            $class_name = ucfirst($class_name);
            $base_class_name = 'Base'.$class_name;

            if (empty($this->_structure[$class_name])) $this->loadStructureFiles(SL::getDirUserLibs() . 'slDB/structure/');
            if (empty($this->_structure[$class_name])) {
                throw new slDBException('There is no structure defined for '.$class_name);
            }
            $base_class_path = $this->_storage_path . 'bases/' . $base_class_name . '.class.php';
            $template = file_get_contents(dirname(__FILE__). '/../model/_base_template.php');

            $structure_array = '$_structure = ' . dumpAsString($this->_structure[$class_name], 1).';';


            $properties = '';
            foreach($this->_structure[$class_name]['columns'] as $key=>$props) {
                $properties .= ' * @property mixed '.$key. "\n";
            }
            if (!empty($this->_structure[$class_name]['relations'])) {
                foreach($this->_structure[$class_name]['relations'] as $key=>$props) {
                    $properties .= ' * @property ' . (isset($props['model']) ? $props['model'] : 'slModel') . ' ' . $key. "\n";
                }
            }
            $methods_text = '';
            $methods = array();
            $abilities = array();
            if (!empty($this->_structure[$class_name]['abilities'])) {
                foreach($this->_structure[$class_name]['abilities'] as $key=>$props) {
                    $ability = slInflector::camelCase($key) . 'Ability';
                    if (!isset($abilities[$ability])) {
                        $abilities[$ability] = new $ability(null);
                        $ability_methods = $abilities[$ability]->getPublishedActions();
                        foreach(array_keys($ability_methods) as $method) {
                            if (!in_array($method, $methods)) $methods[] = $method;
                        }
                    }
                }
                foreach($methods as $method) {
                    $methods_text .= ' * @method ' . $method . '() ' . $method. "()\n";
                }
            }
            $replace = array(
                '__BASENAME__'              => $base_class_name,
                '__NAME__'                  => $class_name,
                '__PROPERTIES__'            => $properties . "\n" . $methods_text,
                '__DATE__'                  => date('d.m.Y H:i:s'),
                '__PROJECT__'               => SL::getProjectConfig('name'),
                '$_structure = array();'    => $structure_array
            );
            $template = str_replace(array_keys($replace), array_values($replace), $template);

            if (is_file($base_class_path)) @unlink($base_class_path);
            if (is_file($base_class_path)) {
                throw new slDBException('Cannot overwrite old Base class for '.$base_class_name);
            }

            file_put_contents($base_class_path, $template);

            if (!is_file($this->_models_path . $class_name . '.class.php')) {
                $template = file_get_contents(dirname(__FILE__). '/../model/_model_template.php');
                $template = str_replace(array_keys($replace), array_values($replace), $template);
                slLocator::makeWritable($this->_models_path);
                file_put_contents($this->_models_path . $class_name . '.class.php', $template);
            }
            slAutoloader::getInstance()->addDir($this->_storage_path . 'bases/');
            slAutoloader::getInstance()->addDir($this->_models_path);

            $this->saveYamlStructure($class_name, $this->_structure[$class_name]);

            if ($rebuild_abilities && !empty($this->_structure[$class_name]['abilities'])) {
                /**
                 *  @var Model
                 */
                $model_instance = new $class_name;
                foreach($this->_structure[$class_name]['abilities'] as $ability_name=>$params) {
                    $ability_name = ucfirst($ability_name);
                    $ability_class = $ability_name.'Ability';
                    if (class_exists($ability_class)) {
                        $this->_abilities[$ability_name] = new $ability_class($model_instance);
                        $this->_abilities[$ability_name]->setUp();
                    }
                }
                $this->_structure[$class_name] = $model_instance->getStructure()->get();
                $this->saveYamlStructure($class_name, $this->_structure[$class_name]);
            }
        }
    }

    /**
     * Dumps current database data to YML files
     * @param string|array $models
     */
    public function dataDump($models = null) {
        if (!empty($models)) {
            if (!is_array($models)) $models = array($models);
        } else {
            $models = array_keys($this->_structure);
        }

        $data_dir = $this->_storage_path . 'data/';
        foreach($models as $model) {
            $model      = ucfirst($model);
            $file_name  = $data_dir . strtolower($model).'.yml';
            if (!isset($this->_structure[$model])) continue;

            if (is_file($file_name)) {
                unlink($file_name);
            }
            $data         = Q::create($this->_structure[$model]['table'])->exec();
            file_put_contents($file_name, sfYaml::dump($data));
        }
    }

    /**
     * Load data from YML files into database with replacing
     * @param string|array $models
     */
    public function dataLoad($models = null) {
    
        if (!empty($models)) {
            if (!is_array($models)) $models = array($models);
        } else {
            $models = array_keys($this->_structure);
        }

        $data_dir = $this->_storage_path . 'data/';
        foreach($models as $model) {
            $model      = ucfirst($model);
            $file_name  = $data_dir . strtolower($model).'.yml';
            if (!isset($this->_structure[$model]) || !is_file($file_name)) continue;
            $data       = sfYaml::load($file_name);
            if (!is_array($data)) continue;

            Q::create('SET FOREIGN_KEY_CHECKS=0');
            Q::execSQL('TRUNCATE `'.$this->_structure[$model]['table'].'`');
            Q::create($this->_structure[$model]['table'])->insert($data)->exec();
        }
    
    }

    /**
     * Generate whole SQL definition for table
     *
     * @param array $structure
     * @return string
     */
    public function generateTableSQL($structure) {
		$sql = 'CREATE TABLE `'.$structure['table'].'` ('."\n";

		foreach($structure['columns'] as $column=>$info) {
            if (!is_array($info)) $info = array('type'=>$info);
		    if (!isset($info['name'])) $info['name'] = $column;
			$sql .= $this->generateColumnSQL($info).','."\n";
		}
		if (!empty($structure['indexes'])) {
            foreach($structure['indexes'] as $name=>$info) {
                if (!isset($info['name'])) $info['name'] = $name;
                $sql .= $this->generateIndexSQL($info).','."\n";
            }
        }
        if (!empty($structure['constraints'])) {
            foreach($structure['constraints'] as $name=>$info) {
                $info['local_table'] = $structure['table'];
                $sql .= $this->generateConstraintSQL($info).','."\n";
            }
        }
		$sql = substr($sql, 0, -2).')';
        $sql .= ' ENGINE = '.(empty($info['engine']) ? 'INNODB' : $info['engine']).'  CHARACTER SET='.(empty($info['charset']) ? 'utf8' : $info['charset']);

		return $sql;   
    }

    /**
     * Generate part of SQL definition for column 
     *
     * @access private
     * @param array $info
     * @return string SQL
     */
	private function generateColumnSQL($info) {
	    $sql = '';
	    if (isset($info['old_name'])) {
	        $sql = '`'.$info['old_name'].'` ';
	    }

		$sql .= '`'.$info['name'].'` '.$info['type'];
		if (isset($info['unsigned'])) $sql .= ' unsigned';
		if (isset($info['zerofill'])) $sql .= ' zerofill';
		if (isset($info['auto_increment'])) {
			$sql .= ' NOT NULL auto_increment';
		} elseif (isset($info['default']) && (strpos($info['default'], '#sql#') === false)) {
			$sql .= ' DEFAULT \''.$info['default']."'";
		} elseif (isset($info['not_null'])) {
			$sql .= ' NOT NULL';
		}
		return $sql;
	}

    /**
     * Generate part of SQL definition for Indexes
     *
     * @access private
     * @param array $info
     * @return string
     */
	private function generateIndexSQL($info) {
		$keys = $info['columns'];
		$sql = '';
        if (!isset($info['type'])) $info['type'] = ($info['name'] == 'primary' ? 'primary' : 'simple');
		switch ($info['type']) {
			case 'primary':
				$sql .= 'PRIMARY KEY (`'.(is_array($keys) ? implode('`, `', $keys) : $keys).'`)';
			break;
			case 'unique':
			case 'simple':
				$sql .= ($info['type'] == 'unique' ? 'UNIQUE ' : '').'KEY '.'`'.$info['name'].'` (`'.(is_array($keys) ? implode('`, `', $keys) : $keys).'`)';
			break;
		}
		return $sql;	
	}

    /**
     * Generate part of SQL definition for Constraints
     *
     * @param array $info
     * @return string
     */
    private function generateConstraintSQL($info) {
        $name = isset($info['name']) ? $info['name'] : $this->generateForeignKeyName($info);
        $sql = 'CONSTRAINT `'.$name.'` FOREIGN KEY (`'.$info['local_field'].'`) REFERENCES `'
                .$info['foreign_table'].'` (`'.$info['foreign_field'].'`) '.
                'ON DELETE '.(empty($info['on_delete']) ? 'SET NULL' : $info['on_delete']).' ON UPDATE '.(empty($info['on_update']) ? 'CASCADE' : $info['on_update']);
        return $sql;
    }

    private function generateForeignKeyName($info) {
        return $info['local_table'].'__'.$info['local_field'].'__'.$info['foreign_field'].'_fk';
    }

    
    /**
     * Fix incorrect structure in case it might be fixed
     *
     * @static
     * @param array $data structure to fix
     * @param bool $one if need to fix only one model
     * @return array fixed data
     */
    static public function fixYamlStructures($data, $one = false) {
        if ($one) {
            $data = array('structure'=>$data);
        }
        foreach($data as $model_name=>$structure) {
            if (!isset($structure['columns']) || !empty($structure['manual'])) continue;

            foreach(array_keys($structure) as $key) {
                if (!in_array($key, self::$_allowed_structure_keys)) {
                    throw new slDBException('Unexpected key in model '.$model_name.': '.$key);
                }
            }

            $pk_field = false;
            foreach($structure['columns'] as $field=>$info) {
                if (!is_array($info)) {
                    $info = array('type'=>$info);
                }
                if (isset($info['auto_increment'])) {
                    $pk_field = $field;
                }
                $structure['columns'][$field] = $info;
            }
            if (!isset($structure['table'])) $structure['table'] = strtolower(slInflector::pluralize($model_name));
            if (!isset($structure['indexes']['primary'])) {
                if (!$pk_field) {
                    if (!isset($structure['columns']['id'])) {
                        $structure['columns'] = array_merge(array('id' => array(
                            'type'              => 'int(11) unsigned',
                            'auto_increment'    => true,
                            'not_null'          => true
                        )), $structure['columns']);
                    }
                    $pk_field = 'id';
                }
                $structure['indexes']['primary'] = array('columns'=>array($pk_field), 'type'=>'primary');
            }
            if (isset($structure['relations'])) {
                // @todo auto constraints generation from relations
                foreach($structure['relations'] as $key=>$item) {
                }
            }

            $data[$model_name] = $structure;
        }

        return $one ? $data['structure'] : $data;
    }

    /**
     * Calculate a lot of variables that is need to work with relations
     * @static
     * @param slModel $model
     * @param array $r relation info
     * @param string $relation_name
     * @return array
     */
    static public function calculateRelationVariables(slModel $model, $r, $relation_name) {
        //@todo improve type detection
        $type           = isset($r['type']) ? $r['type'] : 'many_to_one';
        $local_table    = $model->getStructure('table');
        $related_model  = null;

        $local_key      = isset($r['local_key']) ? $r['local_key'] : $model->getStructure()->getPrimaryField();
        if (isset($r['model'])) {
            $ms = new slModelStructure($r['model']);
            $related_model = $r['model'];
        } else {
            $ms = null;
        }
        $foreign_table  = isset($r['table']) ? $r['table'] : ($ms ? $ms['table'] : $relation_name);

        if (($type == 'one_to_many') || ($type == 'one_to_one')) {
            $auto_local_field = isset($ms['columns']['id_'.slInflector::directorize($model->getClassName())]) ? 'id_'.slInflector::directorize($model->getClassName()) : $local_key;
        } elseif($type == 'many_to_one') {
            $auto_local_field = $model->getStructure()->isColumnExists('id_'.slInflector::directorize($related_model)) ? 'id_'.slInflector::directorize($related_model) : $local_key;
        } elseif($type == 'many_to_many') {
            $auto_local_field = 'id_'.slInflector::directorize($model->getClassName());
            $auto_foreign_field = 'id_'.slInflector::directorize($ms ? $ms->getClassName() : slInflector::singularize($relation_name));
        }

        $local_field    = isset($r['local_field']) ? $r['local_field'] : $auto_local_field;

        $foreign_field  = isset($r['foreign_field']) ? $r['foreign_field'] : (!empty($auto_foreign_field) ? $auto_foreign_field : $local_field);
        $foreign_key    = isset($r['foreign_key']) ? $r['foreign_key'] : ($ms ? $ms->getPrimaryField() : $foreign_field);


        $alias          = isset($r['alias']) ? $r['alias'] : $relation_name;
        $many_table     = isset($r['many_table']) ? $r['many_table'] : ($local_table > $foreign_table ? $local_table.'_'.$foreign_table : $foreign_table.'_'.$local_table);

        $res = array(
            'local_key'         => $local_key,
            'local_field'       => $local_field,
            'local_table'       => $local_table,
            'foreign_field'     => $foreign_field,
            'foreign_key'       => $foreign_key,
            'foreign_table'     => $foreign_table,
            'alias'             => $alias,
            'type'              => $type,
            'many_table'        => $many_table,
            'related_model'     => $related_model,
        );
        return $res;
    }

}
