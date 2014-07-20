<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 01.12.2009 20:14:25
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * ModelCollection class
 *
 * @version 1.0
 *
 * @method slModelCollection loadRelative($relation = null)
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slModelCollection extends ArrayIterator {

    /**
     * @var array store objects for collection
     */
    protected $_objects         = array();

    /**
     * @var array keys for access to collection
     */
    protected $_keys            = array();

    /**
     * @var int key for arrayIterator
     */
    protected $_pos             = 0;

    /**
     * @var array associative map for pk<->index
     */
    protected $_pk_map          = array();

    /**
     * @var array associative map with user field
     */
    protected $_user_map        = array();

    /**
     * @var slModel|string instance of slModel of current model
     */
    protected $_model           = null;

    /**
     * @var array abilities classes loaded for current model
     */
    protected $_loaded_abilities        = array();

    protected $_abilities_actions       = array();

    /**
     * @var bool model collection is new
     */
    protected $_is_new          = true;

    /**
     * @var
     */
    protected $_parent_object   = null;

    /**
     * @var string current model name
     */
    protected $_model_name      = null;

    /**
     * @var string name of primary field of current model
     */
    protected $_pk              = null;

    /**
     * @var bool type of access via array iterator
     */
    protected $_work_by_pk      = false;

    private $sort_field = null;
    private $sort_type  = null;
    private $sort_is_string = false;

    protected $_pre_savers      = array();
    protected $_post_savers     = array();
    protected $_pre_laoders     = array();
    protected $_post_loaders    = array();

    /**
     * Initialize ModelCollection with data
     * @param slModel|string $model instance of slModel or class name
     * @param array $objects
     */
    public function __construct($model, $objects = array()) {
        if (!is_object($model)) {
            $model = new $model();
        }
        $this->_model              = $model;
        $this->_model_name         = $model->getClassName();
        $this->_pk                 = $model->getStructure()->getPrimaryField();
        $this->addObjects($objects);

        // initialize position for array iterator
        $this->_pos = 0;
        if ($this->_model->getStructure('abilities')) {
            foreach($this->_model->getStructure('abilities') as $ability_name=>$params) {
                $ability_name = ucfirst($ability_name);
                $ability_class = $ability_name.'Ability';
                if (class_exists($ability_class)) {
                    $this->_loaded_abilities[$ability_name] = new $ability_class($this->_model);
                    $this->_loaded_abilities[$ability_name]->bootstrap();
                    if (method_exists($this->_loaded_abilities[$ability_name], 'postLoad')) {
                        $this->_post_loaders[] = array($this->_loaded_abilities[$ability_name], 'postLoad');
                    }
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

                }
            }
        }

        if ($this->_model->getStructure('relations')) {
            $this->_loaded_abilities['Relative'] = new RelativeAbility($this->_model);
        }

    }

    public function idInCollection($id) {
        return (array_key_exists($id, $this->_pk_map));
    }

    /**
     * Activate IDS as keys for array access iterator
     * @param bool $is_active
     * @return slModelCollection
     */
    public function switchPKAccess($is_active = true) {
        $this->_work_by_pk = $is_active;
        return $this;
    }

    /**
     * Return current model name
     * @return null|string
     */
    public function getModelName() {
        return $this->_model_name;
    }

    /**
     * Return data of loaded objects as array
     * @param bool $deep
     * @param string|null $index_field
     * @return array
     */
    public function toArray($deep = false, $index_field = null) {
        $res = array();
        foreach($this->_objects as $object) {
            if ($index_field && ($key = (string)$object[$index_field])) {
                $res[$key] = $object->toArray($deep);
            } else {
                $res[] = $object->toArray($deep);
            }
        }

        return $res;
    }

    /**
     * Return slModelCollection with specified by pks Models
     *
     * @param array $pk_ids
     * @return slModelCollection
     */
    public function getSubCollection($pk_ids) {
        if (!is_array($pk_ids)) $pk_ids = array($pk_ids);
        $return = array();
        foreach($this->_pk_map as $id=>$mix) {
            if (in_array($id, $pk_ids)) {
                $return[$id] = $this->_objects[$this->_pk_map[$id]];
            }
        }

        return new slModelCollection($this->_model, $return);
    }

    /**
     * Return mapped sub collection
     * @param $map_values
     * @return slModelCollection
     */
    public function getSubCollectionMapped($map_values) {
        $return = array();

        foreach($map_values as $id) {
            if (isset($this->_user_map[$id])) {
                $return[$id] = $this->_objects[$this->_user_map[$id]];
            }
        }
        return new slModelCollection($this->_model, $return);
    }

    public function getIndexedBy($field) {
        $result = array();
        foreach($this->_objects as $key=>$object) {
            $result[$object[$field]] = $object;
        }
        return $result;
    }


    /**
     * Return one slModel from Collection
     *
     * @param mixed $pk_id
     * @return slModel
     */
    public function getOne($pk_id) {
        if (isset($this->_pk_map[$pk_id])) {
            return $this->_objects[$this->_pk_map[$pk_id]];
        }
        return null;
    }

    /**
     * @param $value
     * @return slModel
     */
    public function getOneMapped($value) {
        if (isset($this->_user_map[$value])) {
            return $this->_objects[$this->_user_map[$value]];
        }
        return null;
    }



    /**
     * Return First slModel from Collection
     * @return slModel
     */
    public function getFirst() {
        $keys = array_keys($this->_pk_map);
        if (!count($keys)) return null;
        return $this->_objects[$this->_pk_map[$keys[0]]];
    }

    /**
     * Return Last object from collection
     * @return null
     */
    public function getLast() {
        $keys = array_keys($this->_pk_map);
        if (!count($keys)) return null;
        return $this->_objects[$this->_pk_map[$keys[count($keys) - 1]]];
    }

    /**
     * Magic method
     * @param $method
     * @param $params
     * @return slModelCollection
     * @throws Exception|slModelException
     */
    public function __call($method, $params) {
        if (array_key_exists($method, $this->_abilities_actions)) {
            return $this->_loaded_abilities[$this->_abilities_actions[$method]['ability']]->$method($this->_objects, $params);
        }

        if (substr($method, 0, 4) == 'load') {
            $operation = 'load';
            $ability = ucfirst(substr($method, 4));
        } elseif (substr($method, 0, 7) == 'process') {
            $operation = 'process';
            $ability = ucfirst(substr($method, 7));
        } else {
            throw new slModelException('Method '.$method.' is not implemented for slModelCollection');
        }

        if ($operation) {
            $ability_class = $ability.'Ability';
            if (class_exists($ability_class)) {
                $this->_loaded_abilities[$ability]->{$operation}($this->_objects, $params);
            } else {
                throw new Exception('Ability "'.$ability.'" not found.');
            }
        }

        return $this;
    }

    /**
     * Give array of primary key values of loaded objects
     * @return array
     */
    public function getPKs() {
        return array_keys($this->_pk_map);
    }

    /**
     * Attach specified models to current objects
     *
     * @param slModelCollection|slModel $rel_objects
     * @param string $relation_name
     * @return slModel
     */
    public function attach($rel_objects, $relation_name = null) {
        $this->_loaded_abilities['Relative']->attach($this->_objects, $rel_objects, $relation_name);
        return $this;
    }

    /**
     * Detach specified models from current objects
     *
     * @param slModelCollection|slModel $rel_objects
     * @param string $relation_name
     * @return slModel
     */
    public function detach($rel_objects, $relation_name = null) {
        $this->_loaded_abilities['Relative']->detach($this->_objects, $rel_objects, $relation_name);
        return $this;
    }

    /**
     * Execute delete method for each object of current colletion
     * @return null
     */
    public function delete() {
        foreach($this->_objects as $key=>$object) {
            $object->delete();
            unset($this->_objects[$key]);
        }
        return null;
    }

    /**
     * Set parent object for whole collection
     * @param $object
     */
    public function setParentObject($object) {
        $this->_parent_object = $object;
    }

    /**
     * Add object to current stack in model collection
     * @param array $objects array of slModel objects or slModelCollection
     * @throws slModelException
     */
    public function addObjects($objects) {

        if (!$this->_model_name) throw new slModelException('No model_name specified for adding objects');
        $pos = $this->_pos;
        $this->_pos = $this->count();
        foreach($objects as $key=>$item) {
            if (!is_object($item)) {
                $object = new $this->_model_name();
                $object->setOriginalData($item);
            } else {
                $object = $item;
            }

            if ($this->_pk && isset($item[$this->_pk])) {
                $this->_pk_map[$item[$this->_pk]] = $this->_pos;
            }
            $this->offsetSet($this->_pos, $object);
            $this->_pos++;
        }
        $this->_pos = $pos;
    }

    /**
     * Add one object to current collection
     * @param $object
     */
    public function addObject($object) {
        $this->addObjects(array(0=>$object));
    }

    /**
     * Generate user map for "mapped" methods access to model collection objects
     * @param $field
     */
    public function map($field) {
        foreach ($this->_objects as $i => $object) {
            $this->_user_map[$object->{$field}] = $i;
        }
        return $this;
    }

    /**
     * Return values of $field of objects from model collection
     * @param string $field
     * @return array
     */
    public function getFieldArray($field) {
        $return = array();
        foreach ($this->_objects as $i => $object) {
            $return[] = $object->{$field};
        }
        return $return;
    }

    /**
     * Sort loaded objects by $field
     * @param string $field
     * @param string $type
     * @return slModelCollection
     */
    public function sortByField($field, $type = 'asc') {
        $this->sort_is_string = false;
        foreach ($this->_objects as $o) {
            if (is_string($o->{$field})) {
                $this->sort_is_string = true;
                break;
            }
        }
        $this->sort_field = $field;
        $this->sort_type  = (in_array(strtolower($type),array('asc','desc'))) ? strtolower($type) : 'asc';
        usort($this->_keys, array($this, '_sort'));
        return $this;
    }

    /**
     * Internal sort function
     * @param $a
     * @param $b
     * @return int
     */
    private function _sort($a, $b) {
        $a = $this->_objects[$this->_keys[$a]]->{$this->sort_field};
        $b = $this->_objects[$this->_keys[$b]]->{$this->sort_field};
        if ($this->sort_is_string) {
            $res = strcmp($a, $b);
            if ($res == 0) return $res;
            if ($this->sort_type == 'asc') {
                return $res > 0 ? 1 : -1;
            } else {
                return $res > 0 ? -1 : 1;
            }
        } else {
            if ($a == $b) return 0;
            if ($this->sort_type == 'asc') {
                return $a > $b ? 1 : -1;
            } else {
                return $a < $b ? -1 : 1;
            }
        }
    }

    // Service Functions
    public function offsetGet($pos) {
        if ($this->_work_by_pk) {
            return $this->getOne($pos);
        } else {
            return $this->_objects[$pos];

        }
    }
    public function offsetSet($pos, $value) {

        if (is_null($pos)) {
            if (!is_object($value)) throw new Exception('Trying to add non-object to ModelCollection');
            $pos = count($this->_objects);
            $this->_pk_map[$value->id] = $pos;
            $this->_parent_object->attach($value);
        }
        if (!isset($this->_objects[$pos])) {
            $this->_keys[] = $pos;
        }
        $this->_objects[$pos] = $value;
    }
    public function offsetExists($pos) {
        return isset($this->_objects[$pos]);
    }
    public function offsetUnset($pos) {
        unset($this->_objects[$pos]);
        foreach($this->_keys as $key=>$value) {
            if ($value == $pos) unset($this->_keys[$key]);
        }
    }

    public function __toString() {
        $result = '';
        if ($this->_model->getStructure('columns/title')) {
            foreach($this->_objects as $object) {
                $result .= $object->title . ', ';
            }
            $result = substr($result, 0, -2);
        } else {
            $result = implode(', ', $this->_pk_map);
        }
        return $result;
    }

    public function rewind () { $this->_pos = 0; }
    public function current () { return $this->_objects[$this->_keys[$this->_pos]]; }
    public function key () { return $this->_keys[$this->_pos]; }
    public function next () { $this->_pos++; }
    public function valid () { return isset($this->_keys[$this->_pos]); }
    public function seek($pos) { $this->_pos = array_search($pos, $this->_keys);}
    public function count() { return count($this->_objects); }
}