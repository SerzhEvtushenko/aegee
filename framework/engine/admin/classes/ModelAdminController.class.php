<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 16.10.12 02:04
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class ModelAdminController extends AdminController {

    protected $_default_value     = '---';

    protected $_model_name = null;

    protected $_loadable_abilities = array(
        'tags'
    );

    /**
     * @var slModelStructure
     */
    protected $_model_structure = null;

    protected $_fields     = array();

    public function preAction() {
        if ($this->_module) {
            $this->_model_name = slInflector::camelCase(isset($this->_module['model']) ? $this->_module['model'] : slInflector::singularize($this->route->get('module')));
            $this->_model_structure = new slModelStructure($this->_model_name);
        } else {
            throw new slAdminException('No Module specified for current url!');
        }
        $this->performFields();

    }

    protected function performFields() {
        $fields = $this->_module['fields'];
        $fields_to_hide = array();
        if (isset($this->_module['actions'][$this->_action]['hide'])) {
            $fields_to_hide = $this->_module['actions'][$this->_action]['hide'];
        }
        $fields_to_show = isset($this->_module['actions'][$this->_action]['show']) ? $this->_module['actions'][$this->_action]['show'] : array();
        foreach($fields as $key=>$item) {
            $field_key = is_numeric($key) ? $item : $key;

            // skip fields that we don't use
            if ((!empty($fields_to_show) && (!in_array($field_key, $fields_to_show))) || in_array($field_key, $fields_to_hide)) continue;

            $this->_fields[$field_key] = self::performFieldStructure($fields, $key, $item);
        }
        $this->view->fields = $this->_fields;
    }

    protected function performFieldStructure($fields, $key, $item) {
        $field_key = is_numeric($key) ? $item : $key;

        $info = $this->_model_structure->getColumn($field_key);
        // for abilities loading
        if (empty($info)) $info = array();

        if (is_array($item)) {
            foreach($item as $k=>$i) {
                $info[$k] = $i;
            }
        }

        if (!isset($info['title'])) {
            $info['title'] = is_array($item) ? ucfirst($field_key) : $item;
            if (!$info['title']) {
                $info['title'] = ucfirst($field_key);
            }
//            $info['title'] = ucfirst($field_key);
        }

        $field_type = isset($info['type']) ? $info['type'] : 'text';
        if ((strpos($field_type, 'int') !== false) || strpos($field_type, 'varchar') !== false) {
            $info['type'] = 'text';
        } elseif (($field_type == 'text') && isset($info['type'])) {
//            $info['type'] = 'rich';
        } elseif (($field_type == 'multiselect') || ($field_type == 'select')) {
            $info['is_object'] = false;
            $info['type'] = $field_type;
            if (!empty($info['model'])) {
                $info['field_values'] = call_user_func(array($info['model'], 'loadList'));
                $info['is_object'] = true;
            } elseif($relation = $this->_model_structure->get('relations/'.$field_key)) {
                $info['field_values'] = call_user_func(array($relation['model'], 'loadList'));
                $info['is_object'] = true;
            }
        } elseif ($field_type == 'tags') {
            $field_values = TagsAbility::getModelTags($this->_model_name);
            $info['field_values'] = $field_values;
        } elseif ($field_type == 'rights_list') {
            $field_values = AclUser::getRightsList();
            $info['field_values'] = $field_values;
        } elseif (isset($info['template'])) {
            $info['type'] = 'custom';
        } elseif (!isset($info['type'])) {
            $info['type'] = 'text';
        }
        if ($info['type'] == 'datetime') {
            $info['type'] = 'date';
        }
        $mlt_columns = $this->_model_structure->get('abilities/mlt/columns');
        if ($this->_model_structure->getColumn('lang')) {
            $info['lang'] = MLT::getActiveLanguage();
        } else if (is_array($mlt_columns) && in_array($key, $mlt_columns)){
                $info['lang'] = MLT::getActiveLanguage();
        }

        return $info;

    }

}
