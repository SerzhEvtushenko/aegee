<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 10.05.12 11:38
 */ 
/**
 * Main admin controller
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
 
class AdminController extends slController {

    protected $_structure   = null;

    protected $_module      = null;

    protected $_action      = null;

    public function __construct(slRoute $route) {
        parent::__construct($route);
        $this->_action = strtolower(substr($this->route->get('action'), 6));
        $this->_structure = sfYaml::load(file_get_contents(dirname(__FILE__) . '/../config/structure.yml'));

        $this->view->structure = $this->_structure;
        $this->view->module = $this->route->get('module');
        if (($module = $this->route->get('module')) && array_key_exists($module, $this->_structure['modules'])) {
            $this->_module = $this->performModuleStructure($this->_structure['modules'][$module]);
            $this->_module['name'] = $module;
            $this->view->module_structure = $this->_module;
            $this->view->action           = isset($this->_module[$this->_action]) ? $this->_module[$this->_action] : array();
        }
    }

    public function actionDefault() {
        $this->view->setTemplate('index/default.tpl');
        $this->view->module = 'dashboard';
    }

    public function actionSettings() {
        $fields_settings = array(
            'meta_title'        => array(
                'title' => 'Основной заголовок',
                'type'  => 'text',
            ),
            'meta_description'  => array(
                'title' => 'Meta Description',
                'type'  => 'area',
            ),
            'meta_keywords'  => array(
                'title' => 'Meta Keywords',
                'type'  => 'area',
            ),
            'email'  => array(
                'title' => 'email',
                'type'  => 'text',
                "validation" => array (
                    "mandatory" => "Введіть ваш email",
                    "email" => true,
                ),
            ),
        );

        $fields_user = array(

            'name'        => array(
                'title' => 'Имя',
                'type'  => 'text',
            ),
            'avatar'      => array(
                'title' => 'Аватар',
                'type'  => 'image',
            ),
        );

        // working with settings
        if ($this->route->getForm('settings')) {
            $data = $this->route->getVar('data');
            SL::setConfig('settings', null, $data, false);
        }
        $this->view->settings_data      = SL::getConfig('settings');
        $this->view->fields_settings    = $fields_settings;

        $this->view->fields_user        = $fields_user;

        // working with user
        $user          = User::loadOne(slACL::getCurrentUser('id'));
        if ($this->route->getForm('user')) {
            $data = $this->route->getVar('data');
            $user->mergeData($data);
            $user->save();
        }
        $this->view->user_data          = $user;

        $this->view->setTemplate('index/settings.tpl');
    }

    public function actionLogout() {
        slACL::logout();
        $this->route->redirectAppIndex();
    }

    protected  function performModuleStructure($structure) {
        return $structure;
    }

    protected function redirectModuleHome() {
        $this->route->redirect('admin/' . $this->route->get('module') . '/');
        die();
    }

}
