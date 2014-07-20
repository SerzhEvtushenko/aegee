<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 12.10.12 10:48
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slAdminStaticController extends ModelAdminController {

    public function preAction(){
        parent::preAction();

        $this->view->module_title = $this->_module['title'];
        $this->view->module_slug  = $this->_module['select']['slug'];
    }

    protected function performModuleStructure($structure) {
        foreach($structure['fields'] as $key=>$item) {
            $field_key = is_numeric($key) ? $item : $key;

            if (is_array($item) && !empty($item['fields'])) {
                foreach($item['fields'] as $sub_key=>$sub_item) {
                    $structure['fields'][$sub_key] = $sub_item;
                    $structure['fields'][$sub_key]['sub_name'] = $field_key;
                }
                unset($structure['fields'][$key]);
            }

        }
        return $structure;
    }

    public function actionDeleteImage() {
        $object = null;
        $where = $this->_module['select'];
        if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $where))) {
            throw new slRouteNotFoundException('Static object not found.');
        }

        $alias = $this->route->getGET('alias');
        $filename = $this->route->getGET('filename');
        $result = $object->removeFile($alias, $filename);
        if (!$this->route->isXHR()) {
            $this->route->redirectReferer();
            die();
        }
        echo json_encode(array('res'=>$result)); die();
    }

    public function actionDefault() {
        $this->view->setTemplate('list/info.tpl');
        /**
         * @var slModel $object
         */
        $object = null;
        $where = $this->_module['select'];
        if (!($object = call_user_func(array($this->_model_name, 'loadOne'), $where))) {
            throw new slRouteNotFoundException('Static object not found.');
        }

        if (isset($this->_module['admin_item']) && (isset($object->_dynamic_structure['columns'])) && ('admin' != slACL::getCurrentUser('login'))){
            throw new slRouteNotFoundException('Static object not found.');
        }

        if ($data = $this->route->getVar('data')) {

            $data_to_merge = array();
            foreach($this->_fields as $field=>$info) {
                if (empty($info['sub_name'])) {
                    if (isset($data[$field])) $data_to_merge[$field] = $data[$field];
                } else {
                    if (!isset($data_to_merge[$info['sub_name']][$field])) $data_to_merge[$info['sub_name']][$field] = array();
                    $data_to_merge[$info['sub_name']][$field] = $data[$field];
                }
            }

            $object->mergeData($data_to_merge);
            if ($object->save(true, true)) {
                $this->view->setMessage('Изменения сохранены');
                $this->route->redirectSelf();
            } else {
                $this->view->errors = $object->getErrors();
            }
        }

        $this->view->data = $object;
    }
}
