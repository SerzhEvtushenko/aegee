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

        $this->processHidingMenuItems();

        $this->view->structure = $this->_structure;

        $this->view->module = $this->route->get('module');

        if (($module = $this->route->get('module')) && array_key_exists($module, $this->_structure['modules'])) {
            $this->_module = $this->performModuleStructure($this->_structure['modules'][$module]);
            $this->_module['name'] = $module;
            $this->view->module_structure = $this->_module;

            $this->checkAccessRights();

            $this->view->action           = isset($this->_module[$this->_action]) ? $this->_module[$this->_action] : array();
        }

        $this->view->show_settings = slACL::hasUserRight('edit_settings');
    }

    private function isModuleAllowed($module) {
        if (!isset($module['access_rights']) || !is_array($module['access_rights'])) return true;
        $allowed = false;
        foreach ($module['access_rights'] as $right) {
            if (slACL::hasUserRight($right)) {
                $allowed = true;
                break;
            }
        }
        return $allowed;
    }

    private function processHidingMenuItems(){
        foreach ($this->_structure['modules'] as &$module) {
            if (!$this->isModuleAllowed($module)) {
                $module['hide_from_menu'] = true;
            }
        }
    }

    private function checkAccessRights(){
        if (!$this->isModuleAllowed($this->_module)) {
            throw new slRouteNotFoundException('No rights in ["'.implode('", "',$this->_module['access_rights']).'"]');
        }
    }

    public function actionDefault() {
        $this->view->setTemplate('index/default.tpl');
        $this->view->module = 'dashboard';
        $this->view->module_title = 'Dashboard';
        $users = Q::create('acl_users')
            ->select('status, count(*) as cn')
            ->groupBy('status')
            ->useValue('cn')
            ->exec();

        $events = Q::create('events')
            ->select('count(*) as cn')
            ->useValue('cn')
            ->one()
            ->exec();
        $news = Q::create('news')
            ->select('count(*) as cn')
            ->useValue('cn')
            ->one()
            ->exec();

        $faq_count = Q::create('faqs')
                ->select('count(id) as cn')
                ->useValue('cn')
                ->one()
                ->exec();

        $user_in_board = Q::create('boards')
            ->select('count(id) as cn')
            ->useValue('cn')
            ->one()
            ->exec();

        $projects_count = Q::create('projects')
            ->select('count(id) as cn')
            ->useValue('cn')
            ->one()
            ->exec();

        $wk_count = Q::create('working_groups')
            ->select('count(id) as cn')
            ->useValue('cn')
            ->one()
            ->exec();


        $user_total_count = 0;
        foreach($users as $count){
            $user_total_count += $count;
        }

        $this->view->site_info  = array(
            'User count'        =>$user_total_count,
            'Events count'      =>$events,
            'News count'        =>$news,
            'Faq count'         => $faq_count,
            'User in board'     =>$user_in_board,
            'Projects count'    =>$projects_count,
            'Working group count'=>$wk_count
        );

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

    static private function genXLS($title, $objects, $fields){
        require_once SL::getDirLibs() . 'PHPExcel/PHPExcel.php';

        $objPHPExcel = new PHPExcel();

        $ch = 'A';
        foreach($fields as $value) {
            $objPHPExcel->getActiveSheet()->setCellValue($ch.'1', $value);
            $ch++;
        }

        $i = 2;
        foreach($objects as $object) {
            $ch = 'A';
            foreach($fields as $value) {
                $objPHPExcel->getActiveSheet()->setCellValue($ch . $i, $object[$value]);
                $ch++;
            }
            $i++;
        }

        $objPHPExcel->getActiveSheet()->setTitle($title);

        /** old excel */
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'_' . date('Y_m_d_H_i_s') . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        /**/
        $objWriter->save('php://output');
        die();
    }

    static public function adminLog($object, $operation){
        $object = isset($object[0]) ? $object[0] : $object;

        if (is_object($object)) {
            $admin_log              = new AdminLog();
            $admin_log->id_object   = (($object->id) && ($object->id > 0)) ? $object->id : 0;
            $admin_log->model       = is_object($object) ? get_class($object) : 'undefined';
            $admin_log->id_user     = slACL::getCurrentUser('id');
            $admin_log->lang        = MLT::getActiveLanguage();
            $admin_log->operation   = $operation;
            $admin_log->device      = AclUser::getDevice();
            $admin_log->ip          = AclUser::getUserIP();
            $admin_log->browser     = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $admin_log->data        = serialize($object->toArray());
            $admin_log->save();
        }

    }
}
