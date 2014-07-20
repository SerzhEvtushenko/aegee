<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 17.10.12 01:33
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class DynamicStructureAbility extends slModelAbility {

    private $_field_structure   = '_dynamic_structure';
    private $_field_values      = '_dynamic_values';

    public $_mixed_methods = array(
        'updateDynamicStructure' => array(),
    );

    public function setUp() {
//        $this->_model->getStructure()->addColumn($this->_field_structure, array(
//            'type'      => 'longtext'
//        ));
        $this->_model->getStructure()->addColumn($this->_field_values, array(
            'type'      => 'longtext'
        ));
        slDBOperator::getInstance(true)->updateDBFromStructure($this->_model->getModelName());
    }

    public function configure() {

    }

    public function postLoad($objects) {
        try {
            $this->requireModeSingle($objects);
            /**
             * @var $object slModel
             */
            $object     = $objects[0];

//            if (!isset($object[$this->_field_structure]) || strlen($object[$this->_field_structure]) == 0) {
            $field_getter = isset($this->_params['field_getter']) ? $this->_params['field_getter'] : 'getID';
            if (!empty($this->_params[$object->{$field_getter}()])) {
                $structure  = $this->_params[$object->{$field_getter}()];
                $values     = array();
//                    $this->_model->setRawValue($this->_field_structure, $structure);
            } else {
                throw new Exception('Some bad stuff happened');
            }
//            } else {
//                $structure  = unserialize($object[$this->_field_structure]);
            if ($serialized_values = unserialize($object[$this->_field_values])) {
                $values = $serialized_values;
            }
//                $this->_model->setRawValue($this->_field_structure, $structure);
//            }
        } catch(Exception $e) {
            return true;
        }

        if (empty($structure)) return true;

        if (!empty($structure['columns'])) {

            foreach($structure['columns'] as $column_name=>$column_info) {
                $column_info['_is_dynamic'] = true;
                $object->setStructure($column_info, 'columns/'.$column_name);
                $object->setRawValue($column_name, !empty($values[$column_name]) ? $values[$column_name] : '');
            }

        }
        if (!empty($structure['abilities'])) {
            foreach($structure['abilities'] as $ability_name=>$ability_info) {
                $object->setStructure($ability_info, 'abilities/'.$ability_name);
            }
            $object->initializeAbilities();
        }
        return $this;
    }

    public function preSave(&$changed, &$all) {


        $dynamic_values = $this->_model->getRawValue($this->_field_values);

        if (!$dynamic_values) {
            $dynamic_values = array();
        } else {
            $dynamic_values = unserialize($dynamic_values);
        }

        $is_dynamic_change = false;

        foreach($changed as $key=>$value) {
            if ($this->_model->getStructure('columns/'.$key.'/_is_dynamic')) {
                $dynamic_values[$key] = $value;
                $is_dynamic_change = true;
                unset($changed[$key]);
            }
        }

        if ($is_dynamic_change) {
            $changed[$this->_field_values] = serialize($dynamic_values);
        }

    }

    public function updateDynamicStructure(&$objects, $params) {
        $this->requireModeSingle($objects);
        $object = array_pop($objects);
        if ($object->isNew()) throw new slDBException('You cannot update dynamic structure to new object');

        $new_structure = $params[0];
        $old_structure = $object->{$this->_field_structure};
        if (empty($old_structure)) {
            $old_structure = $new_structure;
        } else {
            $old_structure = unserialize($old_structure);
            SL::extendDeepArrayValue($old_structure, $new_structure);
        }
        $object->{$this->_field_structure} = $old_structure;
        $object->save(false,true);
        return true;
    }


}
