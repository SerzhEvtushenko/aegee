<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 12.11.12 13:20
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class StartupController extends slAdminStartupController {

    public function preAction() {

        parent::preAction();

        if (slACL::isLoggedIn() && !slACL::hasUserRight('administrator') && slACL::hasUserRight('root')){
            $this->route->redirectIndex();
        }

        if ($el = $this->route->getPOST('editing_language')) {
            MLT::setActiveLanguage($el);
        }
        $this->view->revision = 1;

        $this->view->admin_title = 'AEGEE';
        
        $this->view->editing_language = MLT::getActiveLanguage();
    }

}
