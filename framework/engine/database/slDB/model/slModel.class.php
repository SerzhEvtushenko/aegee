<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 28.11.2009 01:33:46
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Model class
 *
 * @version 1.0
 *
 * @method slModel loadRelative($what = null) loadRelative($what = null) Load related Models or data
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
abstract class slModel extends ArrayIterator {

    /**
     * For internal use
     */
    const CREATION_MODE_INTERNAL    = 'internal';

    /**
     * @var C Criteria that slModel was loaded with
     */
    protected $_load_criteria       = null;

    /**
     * @var slModelStructure
     */
    protected $_structure           = array();

    /**
     * @var string key field name
     */
    protected $_key_field           = null;

    /**
     * @var boolean identify that model is new and no data stored into DB
     */
    protected $_is_new              = true;

    /**
     * @var bool if we just save our object
     */
    protected $_is_first_save       = true;

    /**
     * @var boolean identify that data of model was changed
     */
    protected $_is_changed          = false;

    /**
     * @var $_data array actual data loaded for model and which user working with
     */
    protected $_data                = array();

    /**
     * @var $_errors array 
     */
    protected $_errors              = array();

    /**
     * @var array for array iterator
     */
    protected $_keys                = array();

    /**
     * @var int for array iterator
     */
    protected $_pos                 = 0;

    /**
     * @var array data that were changed
     */
    protected $_changed_data        = array();

    /**
     * @var array original data loaded from database
     */
    protected $_original_data       = array();

    /**
     * @var array getters already invoked to disable double calling
     */
    protected $_invoked_getters     = array();

    /**
     * @var array Instances of abilities for current model
     */
    protected $_loaded_abilities    = array();

    /**
     * @var array Loaded relations
     */
    protected $_loaded_relatives    = array();

    /**
     * @var array store correct published abilities actions
     */
    protected $_abilities_actions   = array();

    /**
     * @var array pre savers method that would be called
     */
    protected $_pre_savers          = array();

    /**
     * @var array post savers method that would be called
     */
    protected $_post_savers         = array();

    /**
     * @var array method that executes after load method executed
     */
    protected $_post_loaders        = array();

    /**
     * @var null|slModel filled when current object has parent related object
     */
    protected $_parent_object       = null;

    /**
     * @var string current model name
     */
    protected $_model_name          = null;

    protected $_custom_validation_rules = array();


    public function __construct($creation_mode = null) {
        $this->_model_name = get_class($this);
        $this->_structure = new slModelStructure($this->_model_name);

        if (isset($this->_structure['indexes']['primary']) && (count($this->_structure['indexes']['primary']['columns']) == 1)) {
            $this->_key_field = !empty($this->_structure['indexes']['primary']['columns'][0]) ? $this->_structure['indexes']['primary']['columns'][0] : null;
        }
//        if ($creation_mode == slModel::CREATION_MODE_INTERNAL) return true;
        $this->initializeAbilities();

        if (!empty($this->_structure['relations'])) {
            $this->_loaded_abilities['Relative'] = new RelativeAbility($this);
        }
        $this->configure();

    }

    public function initializeAbilities() {
        if (!empty($this->_structure['abilities'])) {
            foreach($this->_structure['abilities'] as $ability_name=>$params) {
                $ability_name = slInflector::camelCase($ability_name);
                $ability_class = $ability_name.'Ability';
                if (class_exists($ability_class) && empty($this->_loaded_abilities[$ability_name])) {
                    $this->_loaded_abilities[$ability_name] = new $ability_class($this);
                    $this->_loaded_abilities[$ability_name]->bootstrap();
                    $actions = $this->_loaded_abilities[$ability_name]->getPublishedActions();
                    foreach($actions as $action=>$info) {
                        if (!is_array($info)) throw new slModelException('Invalid loaded Relation:'.$action);

                        $this->_abilities_actions[$action] = array_merge($info, array(
                            'ability'  =>   $ability_name
                        ));
                    }
                    if (method_exists($this->_loaded_abilities[$ability_name], 'preSave')) {
                        $this->_pre_savers[] = array($this->_loaded_abilities[$ability_name], 'preSave');
                    }
                    if (method_exists($this->_loaded_abilities[$ability_name], 'postSave')) {
                        $this->_post_savers[] = array($this->_loaded_abilities[$ability_name], 'postSave');
                    }
                    if (method_exists($this->_loaded_abilities[$ability_name], 'postLoad')) {
                        $this->_post_loaders[] = array($this->_loaded_abilities[$ability_name], 'postLoad');
                    }
                } else {
                    //vd($ability_class,2);
                }
            }
        }
    }

    /**
     * Create and return instance of object specified in $model_name
     * @static
     * @param  $model_name
     * @return slModel
     */
    static public function getModel($model_name) {
        return new $model_name();
    }

    /**
     * Loading one object from database and run postLoad method
     * @access protected
     * @param  $criteria
     * @return slModel
     */
    protected function _loadOne($criteria = null, $query_part = null) {
        $this->_is_first_save = false;
        $q = Q::create($this->_structure['table'])->one();
        if ($criteria) {

            if (!is_array($criteria) && !is_object($criteria)) {
                $criteria = array( $this->getStructure('table') . '.' . $this->_structure->getPrimaryField() => $criteria);
            } elseif (is_array($criteria)) {
                $new_criteria = array();
                foreach($criteria as $c_key=>$c_value) {
                    $new_criteria[ $this->getStructure('table') . '.' . $c_key] = $c_value;
                }
                unset($criteria);
                $criteria = $new_criteria;
                unset($new_criteria);
            }

            $q->andWhere($criteria);
            $this->_load_criteria = $criteria;
        } else {
            $criteria = new C();
        }

        if ($this->getAbilitiesStructure('mlt')) {
            $query_part = $this->_loaded_abilities['Mlt']->updateLoadQueryPart($query_part);
        }

        if ($query_part) {
            $q->merge($query_part);
        }
        /**
         * Security check
         */
        $secured = $this->getMethodSecurity('load');
        if ($secured) {
            $secured['full_access'] = false;
            try {
                slACL::requireRight(strtolower($this->_model_name) . '_load');
                $secured['full_access'] = true;
            } catch (slACLUnaccreditedException $e) {
                throw new $e($e->getMessage());
            }
        }
        if (($data = $q->exec())) {
            $this->setOriginalData($data);
            if ($secured && !$secured['full_access']) {
                if ($secured['process'] == 'custom') {
                    $method = isset($secured['method']) ? $secured['method'] : 'hasSecuredMethodAccess';
                    if (!$this->$method('load')) {
                        throw new slACLUnaccreditedException(strtolower($this->_model_name) . '_load');
                    } else {
                        slACL::requireObjectRight($this->_model_name, $this->getID(), 'load');
                    }
                }
            }
            $this->_postLoad();
            return $this;
        } else {
            return null;
        }


    }

    /**
     * Method need to be overrided in Model Realization if used
     * @throws slModelException
     */
    public function getSecutiryLoadFilter() {
        throw new slModelException('You need to override getSecurityLoadFilter() into you '.get_class($this).' model!');
    }

    /**
     * @access protected
     * @param C $criteria
     * @return slModelCollection
     */
    protected function _loadList($criteria = null, $query_part = null) {
        $this->_is_first_save = false;
        $q = Q::create($this->_structure['table']);
        $list = null;
        if (empty($criteria)) $criteria = C::create();
        if (is_array($criteria) && array_key_exists(0, $criteria)) {
            $criteria = array($this->getStructure('table') . '.' . $this->_structure->getPrimaryField() => $criteria);
        } elseif(is_object($criteria)) {
            if ($criteria instanceof slModelCollection) {
                $criteria = array($this->getStructure('table') . '.' . $this->_structure->getPrimaryField() => $criteria->getPKs());
            } elseif(!($criteria instanceof C)) {
                throw new slModelException('Unimplemented object in LoadList');
            }
        } elseif (is_string($criteria)) {
            $criteria = array($this->getStructure('table') . '.' . $this->_structure->getPrimaryField() => $criteria);
        } elseif (is_array($criteria)) {
            $new_criteria = array();
            foreach($criteria as $c_key=>$c_value) {
                $new_criteria[ $this->getStructure('table') . '.' . $c_key] = $c_value;
            }
            unset($criteria);
            $criteria = $new_criteria;
            unset($new_criteria);
        }
        $q->andWhere($criteria);

        if ($this->getAbilitiesStructure('mlt')) {
            $query_part = $this->_loaded_abilities['Mlt']->updateLoadQueryPart($query_part);
        }


        if ($query_part) {
            $q->merge($query_part);
        }

        if (!$q->getCriteria()->getModifier('orderBy')) {
            if ($this->_structure->isColumnExists('_position')) {
                $order_field = '_position';
            } elseif ($this->_structure->isColumnExists('_created_at')) {
                $order_field = '_created_at DESC';
//            } elseif ($this->_structure->isColumnExists('title')) {
//                $order_field = 'title';
            } elseif (isset($this->_structure['params']['order'])) {
                $order_field = $this->_structure['params']['order'];
            } else {
                $order_field = $this->_structure->getPrimaryField();
            }
            if ($order_field) $q->orderBy($order_field);
        }

        /**
         * Security check
         */
        $secured = $this->getMethodSecurity('load');

        if ($secured) {
            $secured['full_access'] = false;
            try {
                slACL::requireRight(strtolower($this->_model_name) . '_load');
                $secured['full_access'] = true;
            } catch (slACLUnaccreditedException $e) {
                if ($secured['filter']) {
                    $method = is_string($secured['filter']) ? $secured['filter'] : 'getSecutiryLoadFilter';
                    $s_criteria = $this->$method();
                    $q->andWhere($s_criteria);
                } else {
                    throw new $e($e->getMessage());
                }
            }
        }

        $this->_load_criteria = $criteria;

        $list = $q->exec();

        if ($list) {
            $mc = new slModelCollection($this, $list);
            return $mc;
        } else {
            $mc = new slModelCollection($this);
            return $mc;
        }
    }

    /**
     * Executed in the end of constructor
     * @return void
     */
    public function configure() {}

    /**
     * internal post load
     */
    protected function _postLoad() {
        if (!empty($this->_structure['relations'])) {
            foreach($this->_structure['relations'] as $relation_name=>$info) {
                if (!empty($info['autoload'])) {
                    $this->loadRelative($relation_name);
                }
            }
        }
        //@todo delete after DynamicAbility
//        foreach($this->_structure['columns'] as $name=>$info) {
//            if (!empty($info['structure'])) {
//                if (!is_array($info['structure'])) {
//                    if (empty($this->_data[$name])) {
//                        $this->_data[$name] = array();
//                    } else {
//                        $this->_data[$name] = unserialize($this->_data[$name]);
//                        foreach($this->_data[$name] as $sub_name=>$sub_value) {
//                            if (!array_key_exists($sub_name, $this->_data)) {
//                                $this->_data[$sub_name] = $sub_value;
//                            }
//                        }
//                    }
//                }
//            }
//        }

        $objects = array(0=>$this);
        foreach($this->_post_loaders as $callable) {
            $callable[0]->$callable[1]($objects);
        }
        $this->postLoad();
    }

    /**
     * Executes after calling load
     * @param $with_validation
     */
    public function postLoad() {}

    /**
     * Executes before calling save
     * @param $with_validation
     */
    public function preSave($with_validation) {}

    /**
     * Executes after calling save
     * @param $with_validation
     */
    public function postSave($with_validation) {}

    /**
     * Save all changed data into database and invoke abilities save
     *
     * @param boolean $with_validation if validation need before save
     * @return boolean
     */
    public function save($with_validation = true, $force_save = false) {
        if (!$this->_is_changed && !$this->_is_new && !$force_save) return true;

        $data = array();

        /**
         * calling pre savers
         */
        foreach($this->_pre_savers as $callable) {
            $callable[0]->$callable[1]($this->_changed_data, $this->_data);
        }

        $this->clearDPTErrors();
        $this->preSave($with_validation);
        if (!empty($this->_errors)) return false;

        /**
         * Security check
         */
        $secured = $this->getMethodSecurity('save');
        if ($secured) {
            try {
                slACL::requireRight(strtolower($this->_model_name) . '_save');
            } catch (slACLUnaccreditedException $e) {
                if ($secured['type'] == 'object') {
                    if ($secured['process'] == 'custom') {
                        $method = isset($secured['method']) ? $secured['method'] : 'hasSecuredMethodAccess';
                        if (!$this->$method('save')) {
                            throw new slACLUnaccreditedException(strtolower($this->_model_name) . '_save');
                        }
                    } else {
                        slACL::requireObjectRight($this->_model_name, $this->getID(), 'save');
                    }
                } else {
                    throw new $e;
                }
            }
        }

        /**
         * Validation check
         */
        if ($with_validation) {
            $validation_result = slValidator::create($this->getValidationRules())->process($this);
            if (!empty($validation_result['errors'])) {
                $this->_errors = $validation_result['errors'];
                return false;
            }
            foreach($validation_result['data'] as $key=>$value) {
                if (!is_array($this->_changed_data)) break;
                /**
                 * overwrite data from processing result
                 */
                if (array_key_exists($key, $this->_changed_data)) {
                    $this->_changed_data[$key] = $this->_data[$key] =  $value;
                }
            }
        }

        /**
         * prepare $data array for inserting to database
         */
        foreach($this->_structure['columns'] as $field=>$info) {
            if (array_key_exists($field, $this->_changed_data)) {
                $data[$field] = $this->_changed_data[$field];
                // FOR EMPTY CHECKBOX IN ADMIN PANEL
            } elseif (!empty($info['default']) && !array_key_exists($field, $this->_data)) {
                if (strpos($info['default'], '#field#') !== false) {
                    $info['default'] = $this->_data[substr($info['default'], 7)];
                }
                $data[$field] = $info['default'];
            } elseif ($force_save) {
                if (($field == 'id') || ($this->_structure->get('columns/'.$field.'/_is_dynamic'))) continue;

                $data[$field] = array_key_exists($field, $this->_data) ? $this->_data[$field] : null;
            }
        }

        $mlt_data = array();
        if ($mlt_info = $this->getAbilitiesStructure('mlt')) {
            foreach($mlt_info['columns'] as $field) {
                if (array_key_exists($field, $data)) {
                    $mlt_data[$field] = $data[$field];
                    unset($data[$field]);
                }
            }
        }
//        vd($data, $mlt_data, $this->_changed_data);

        /**
         * if it's new object - create it, else - update
         */
        if ($this->_is_new) {
            if (empty($data)) {
                $data['id'] = null;
            }
            $this->_data[$this->_key_field] = Q::create($this->_structure['table'])->insert($data)->exec();
        } elseif (count($data)) {
            $this->_is_first_save = false;
            $where = $this->_key_field ? array($this->_key_field => $this->_data[$this->_key_field]) : $this->_original_data;
            Q::create($this->_structure['table'])->update($data)->where($where)->exec();
        }

        if (!empty($mlt_data)) {
            $this->_is_first_save = false;
            $this->_loaded_abilities['Mlt']->setTranslatedData($mlt_data);
        }

        /**
         * Load data from database if it was just created object
         */
        if ($this->_is_new) {
            if ($this->_key_field) {
                $db_data_query = Q::create($this->_structure['table'])->where(array($this->getStructure('table') . '.' . $this->_key_field=>$this->_data[$this->_key_field]))->one();
                if ($this->getAbilitiesStructure('mlt')) {
                    $this->_loaded_abilities['Mlt']->updateLoadQueryPart($db_data_query);
                }
                $db_data = $db_data_query->exec();
                foreach($db_data as $db_key=>$db_value) {
                    $this->_data[$db_key] = $db_value;
                }
            }
        }
        /**
         * working with relations - execute attach and detach methods
         */
        if (isset($this->_structure['relations'])) {
            foreach($this->_structure['relations'] as $relation_name=>$info) {
                if (array_key_exists($relation_name, $this->_changed_data)) {
                    if (isset($this->_changed_data[$relation_name])) {
                        $this->attach($this->_changed_data[$relation_name], $relation_name);
                    } else {
                        $this->detach(null, $relation_name);
                    }
                }
            }
        }

        /**
         * calling post savers
         */
        foreach($this->_post_savers as $callable) {
            $callable[0]->$callable[1]($this->_changed_data, $this->_data);
        }

        $this->postSave($with_validation);

        $this->_is_new = false;
        $this->_changed_data = array();
        $this->_is_changed = false;
        
        return true;
    }

    /**
     * clearing data processing tools errors
     */
    public function clearDPTErrors() {
        foreach($this->_errors as $field=>$rules) {
            foreach($rules as $rule=>$info) {
                if (!empty($info['dpt'])) {
                    unset($this->_errors[$field][$rule]);
                }
            }
            if (empty($this->_errors[$field])) unset($this->_errors[$field]);
        }
    }

    /**
     * Return security status for specified method
     * @param string $method
     * @return array|bool
     */
    public function getMethodSecurity($method) {
        $secured = $this->_structure->get('secured');
        $res = false;


        if (slACL::isActive() && !empty($secured)) {
            if (isset($secured[$method])) {
                $res = is_array($secured[$method]) ? $secured[$method] : array('type'=>'method');
            }
        }
        return $res;
    }

    /**
     * @param $rules array with field=>rules
     */
    public function addValidationRules($rules) {
        foreach($rules as $field=>$info) {
            if (!isset($this->_custom_validation_rules[$field])) $this->_custom_validation_rules[$field] = array(
                'validation'    => array(),
                'process'       => array(),
            );
            if (isset($info['validation'])) {
                $this->_custom_validation_rules[$field]['validation'] += $info['validation'];
            }
            if (isset($info['process'])) {
                $this->_custom_validation_rules[$field]['process'] += $info['process'];
            }
        }
    }

    /**
     * Generate some obvious validation rules
     * @param null|string $field
     * @return array
     */
    public function getValidationRules($field = null) {
        $rules = array();

        foreach($this->_structure['columns'] as $column=>$info) {
            if ($field && $column != $field) continue;
            $rules[$column] = array(
                'validation'    => array(),
                'process'       => array()
            );
            if (!is_array($info)) continue;

            if (isset($info['process'])) {
                $rules[$column]['process'] = $info['process'];
            }
            if (isset($info['validation'])) {
                $rules[$column]['validation'] = $info['validation'];
            }

            if (isset($info['not_null']) && !isset($info['default']) && !isset($info['auto_increment']) && !array_key_exists('mandatory', $rules[$column]['validation'])) {
                $rules[$column]['validation'] = array_merge(array('mandatory' => true), $rules[$column]['validation']);
            }
//            if (isset($info['default']) && !array_key_exists('default', $rules[$column]['process'])) {
//                $rules[$column]['process']['default'] = $info['default'];
//            }

            if (($info['type'] == 'date')) {
                $rules[$column]['process']['auto_date_format'] = true;
            }
            // @todo deep array merge for validation
            if (isset($this->_custom_validation_rules[$column]['validation'])) {
                $rules[$column]['validation'] += $this->_custom_validation_rules[$column]['validation'];
            }
            if (isset($this->_custom_validation_rules[$column]['process'])) {
                $rules[$column]['process'] += $this->_custom_validation_rules[$column]['process'];
            }
        }
        foreach($this->_structure['indexes'] as $key=>$info) {
            if ($key == 'primary') continue;
            
            if (($info['type'] == 'unique') && (count($info['columns']) == 1) && ($this->isNew())) {
                $rules[$info['columns'][0]]['validation'] = array_merge($rules[$info['columns'][0]]['validation'], array(
                    'unique'=>array('table'=>$this->_structure['table'], 'field'=>$info['columns'][0])
                ));
            }
        }
        return $rules;
    }

    /**
     * Return errors that was set with validators
     * @return array
     */
    public function getErrors() {
        return $this->_errors;
    }

    /**
     * Generate custom error for current object
     * @param $field
     * @param $rule
     * @param $message
     */
    public function addError($field, $rule, $message) {
        if (!isset($this->_errors[$field])) $this->_errors[$field] = array();
        $this->_errors[$field][$rule] = array(
            'message'   => $message
        );
    }

    /**
     * Parse array $data for fields and set it to current model
     *
     * @param array $data
     * @return slModel
     */
	public function mergeData($data) {
        if (empty($data)) return $this;
		foreach($data as $key=>$value) {
			if ((!array_key_exists($key, $this->_data) && ($key != $this->_key_field)) ||
                  ($key != $this->_key_field) &&
                  ($value != $this->_data[$key]) &&
                  (isset($this->_structure['columns'][$key])
                    || isset($this->_structure['relations'][$key])
                    || isset($this->_structure['abilities'][$key]))) {
                $this->offsetSet($key, $value);
            } else {
                unset($data[$key]);
            }
		}
        return $this;
	}

    /**
     * Return all data of current model as array
     *
     * @param boolean $deep
     * @return array
     */
    public function toArray($deep = false) {
        $res = $this->_data;
        if ($deep) {
            foreach($res as $key=>$value) {
                if ($value instanceof slModel || $value instanceof slModelCollection) {
                    $res[$key] = $value->toArray(true);
                }
            }
            if ($this->_structure->get('abilities')) {
                foreach($this->_structure->get('abilities') as $ability=>$params) {
                    $res[$ability] = $this->$ability;
                }
            }
        }

        return $res;
    }

    /**
     * @param string $what
     * @param mixed $default default value
     * @return string|slModelStructure
     */
    public function getStructure($what = null, $default = null) {
        $res = $what ? SL::getDeepArrayValue($this->_structure, $what) : $this->_structure;
        return $res ? $res : $default; 
    }

    /**
     * Return abilities list for current Model
     * also return empty array() if no abilities found
     *
     * @param string $what name of ability/structure to return
     * @return mixed|slModelStructure|array
     */
    public function getAbilitiesStructure($what = '') {
        return $this->getStructure('abilities/'.$what, array());
    }

    /**
     * Set specified $value with key $what using DeepArray function
     *
     * @param mixed $value
     * @param string $what
     * @return mixed
     */
    public function setStructure($value, $what = null) {
        return $this->_structure->set($value, $what);
    }

    /**
     * Service function, using for initialize object
     * @param array $data
     * @return slModel
     */
    public function setOriginalData($data) {
        if (!$data) return $this;
        $this->_data = $data;
        $this->_keys = array_keys($this->_data);
        $this->_original_data = $this->_data;

        $this->_is_new = false;
        return $this;
    }

    /**
     * Return original data loaded from DB for current object
     * @param null $key
     * @return mixed
     */
    public function getOriginalData($key = null) {
        return SL::getDeepArrayValue($this->_original_data, $key);
    }

    /**
     * Using for set some object as parent
     * @param slModel $object
     */
    public function setParentObject($object) {
        $this->_parent_object = $object;
    }

    /**
     * Return criteria that was used for loading current slModel
     * @return C Criteria
     */
    public function getLoadCriteria() {
        return $this->_load_criteria;
    }
    
    /**
     * @return string name of current slModel
     */
    public function getClassName() {
        return $this->_model_name;
    }

    /**
     * Attach specified models to current object
     * 
     * @param slModelCollection|slModel $rel_objects
     * @param string $relation_name
     * @return slModel
     */
    public function attach($rel_objects, $relation_name = null) {
        $objects = array(0=>$this);
        if (!$relation_name) $relation_name = strtolower(get_class(is_array($rel_objects) ? $rel_objects[0] : $rel_objects));
        $this->_loaded_abilities['Relative']->attach($objects, $rel_objects, $relation_name);

        $this->clearHash($relation_name);
        return $this;
    }

    /**
     * Detach specified models from current object
     *
     * @param slModelCollection|slModel $rel_objects
     * @param string $relation_name
     * @return slModel
     */
    public function detach($rel_objects, $relation_name = null) {
        $objects = array(0=>$this);
        if (!$relation_name) $relation_name = strtolower(get_class(is_array($rel_objects) ? $rel_objects[0] : $rel_objects));
        $this->_loaded_abilities['Relative']->detach($objects, $rel_objects, $relation_name);

        $this->clearHash($relation_name);

        return $this;
    }

    /**
     * Delete current object, detach all relations and drop data from database
     * @return boolean|slModel
     */
    public function delete() {
        if ($this->_is_new) return false;

        if (isset($this->_data[$this->_key_field])) {
            $where = array($this->_key_field=>$this->_data[$this->_key_field]); 
        } elseif ($this->_load_criteria) {
            $where = $this->_load_criteria;
        } else {
            return false;
        }

        Q::create($this->_structure['table'])->delete($where)->exec();
        if (isset($this->_structure['relations'])) {
            foreach($this->_structure['relations'] as $relation_name=>$info) {
                $this->detach(null, $relation_name);
            }
        }
        if (isset($this->_structure['abilities'])) {
            foreach($this->_structure['abilities'] as $ability_name=>$info) {
                $ability_name = ucfirst($ability_name);
                if (!is_object($this->_loaded_abilities[$ability_name])) continue;
                $objects = array(&$this);
                $this->_loaded_abilities[$ability_name]->unlink($objects);
            }
        }
        return null;
    }

    /**
     * Check for changed data
     * @param null $field
     * @return bool
     */
    public function isChanged($field = null) {
        return $field ? array_key_exists($field, $this->_changed_data) : $this->_is_changed;
    }

    /**
     * Check that object is just created and not stored
     * @return bool
     */
    public function isNew() {
        return $this->_is_new;
    }

    /**
     * Magic method
     * @param $method
     * @param $params
     * @return slModel
     * @throws Exception|slModelException
     */
    public function __call($method, $params) {
        if (array_key_exists($method, $this->_abilities_actions)) {
            $objects = array($this->id=>$this);
            return $this->_loaded_abilities[$this->_abilities_actions[$method]['ability']]->$method($objects, $params);
        }

        if (substr($method, 0, 3) == 'get') {
            $field = strtolower(substr($method, 3));
            if (isset($this->_data[$field])) {
                return $this->_data[$field];
            } else {
                throw new slModelException('Field '.$field.' is not exists for current model');
            }
        } elseif (substr($method, 0, 3) == 'set') {
            $field = strtolower(substr($method, 3));
            $this->_data[$field] = $params[0];
            $this->_changed_data[$field] = $params[0];
            return $this;
        }

        if (substr($method, 0, 4) == 'load') {
             $operation = 'load';
             $ability = ucfirst(substr($method, 4));
         } elseif (substr($method, 0, 7) == 'process') {
             $operation = 'process';
             $ability = ucfirst(substr($method, 7));
         } else {
             throw new slModelException('Method '.$method.' is not implemented for slModel');
         }

         if ($operation) {
             $ability_class = $ability.'Ability';

             if (class_exists($ability_class)) {
                 $objects = array(0=>$this);
                 $this->_loaded_abilities[$ability]->{$operation}($objects, $params);
                 if ($ability == 'Relative') {
                     if (is_string($params)) {
                         $params = explode(',', $params);
                     }
                     foreach($params as $param) {
                         $this->_loaded_relatives[trim($param)] = true;
                     }
                 }
             } else {
                 throw new Exception('Ability "'.$ability.'" not found.');
             }
         }

        return $this;
    }

    /**
     * Return primary key field
     * @return null|string
     */
    public function getPKField() {
        return $this->_key_field;
    }

    /**
     * Return the value of primary key field
     * @return null
     */
    public function getID() {
        return array_key_exists($this->_key_field, $this->_data) ? $this->_data[$this->_key_field] : null;
    }

    /**
     * Return raw valu loaded form database
     * @param $key
     * @return null
     */
    public function getRawValue($key) {
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
    }

    /**
     * Overwrite data in internal array of data loaded from database
     * @param $key
     * @param $value
     */
    public function setRawValue($key, $value) {
        $this->_data[$key] = $value;
    }

    /**
     * Erase $key from data. Internal funciton
     * @param $key
     */
    private function clearHash($key) {
        if (array_key_exists($key, $this->_data)) unset($this->_data[$key]);
        if (array_key_exists($key, $this->_loaded_relatives)) unset($this->_loaded_relatives[$key]);
    }

    /**
     * Setter for current data
     * @param $key
     * @param $value
     * @return slModel
     */
    public function __set($key, $value) {
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * Getter for current data
     * @param $key
     * @return null
     */
    public function &__get($key) {
        $getter_name = slInflector::camelCase($key);
        $method = 'get'.$getter_name;
        $default = null;

        if (method_exists($this, 'get'.$getter_name) && !array_key_exists($getter_name, $this->_invoked_getters)) {
            $this->_data[$key] = $this->_invoked_getters[$getter_name] = $this->{'get'.$getter_name}();
            return $this->_data[$key];
        }
        if (!array_key_exists($key, $this->_data)) {

            if (!array_key_exists($key, $this->_loaded_relatives) && isset($this->_structure['relations'][$key])) {
                if ($this->isNew()) return $default;
                $this->loadRelative($key);
            } elseif (isset($this->_abilities_actions['get'.$getter_name])) {
                $objs = array(&$this);
                $this->_data[$key] = $this->_loaded_abilities[$this->_abilities_actions[$method]['ability']]->$method($objs);
            } elseif (isset($this->_structure['abilities']['files'][$key])) {
                $this->loadFiles();
            } else {
                return $default;
            }

        }
        return $this->_data[$key];

    }

    public function getModelName() {
        return $this->_model_name;
    }

    public function __toString() {
        return $this->title ? $this->title : $this->_model_name;
    }

    // Service Functions
    public function offsetGet($key) {

        $getter_name = slInflector::camelCase($key);
        $method = 'get'.$getter_name;
        if (method_exists($this, 'get'.$getter_name) && !array_key_exists($getter_name, $this->_invoked_getters)) {
            $this->_data[$key] = $this->_invoked_getters[$getter_name] = $this->{'get'.$getter_name}();
            return $this->_data[$key];
        }
        if (!array_key_exists($key, $this->_data)) {
            if (!array_key_exists($key, $this->_loaded_relatives) && isset($this->_structure['relations'][$key])) {
                if ($this->isNew()) return null;
                $this->loadRelative($key);
            } elseif (isset($this->_abilities_actions['get'.$getter_name])) {
                $objs = array(&$this);
                $this->_data[$key] = $this->_loaded_abilities[$this->_abilities_actions[$method]['ability']]->$method($objs);
            } elseif (isset($this->_structure['abilities']['files'][$key])) {
                $this->loadFiles();
            } else {
                return null;
            }

        }
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
    }
    public function offsetSet($pos, $value) {
        if (!isset($this->_data[$pos])) {
            $this->_keys[] = $pos;
        }
        if (array_key_exists($pos, $this->_data) && ($this->_data[$pos] === $value)) return false;
        if (method_exists($this, 'set'.slInflector::camelCase($pos))) {
            $value = $this->{'set'.slInflector::camelCase($pos)}($value);
        }
        $this->_data[$pos] = $value;
        $this->_changed_data[$pos] = $value;
        $this->_is_changed = true;
        return $this;
    }

    public function offsetExists($pos) {
        $getter_name = slInflector::camelCase($pos);
        return method_exists($this, 'get'.$getter_name) || array_key_exists($pos, $this->_data) || isset($this->_structure['relations'][$pos]) || isset($this->_abilities_actions['get'.$getter_name]) || isset($this->_structure['abilities']['files'][$pos]);
    }
    public function offsetUnset($pos) {
        unset($this->_data[$pos]);

        foreach($this->_keys as $key=>$value) {
            if ($value == $pos) unset($this->_keys[$key]);
        }
        $this->_changed_data[$pos] = null;
        $this->_is_changed = true;
    }
	public function rewind () { $this->_pos = 0; }
	public function current () { return $this->_data[$this->_keys[$this->_pos]]; }
	public function key () { return $this->_keys[$this->_pos]; }
	public function next () { $this->_pos++; }
	public function valid () { return array_key_exists($this->_pos, $this->_keys); }
	public function seek($pos) { $this->_pos = array_search($pos, $this->_keys);}
	public function count() { return count($this->_keys); }

}
