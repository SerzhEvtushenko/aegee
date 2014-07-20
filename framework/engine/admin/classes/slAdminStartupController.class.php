<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 29.05.12 17:36
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slAdminStartupController extends slController {

    public function preAction() {
        slView::getInstance()->getActiveEngine()->getHandler()->addPluginsDir(dirname(__FILE__) . '/../helpers/');
        slACL::requireAuthorization();
        $this->view->assign('mlt_languages', MLT::getLanguagesAliases());
        if ($lang = $this->route->getVar('set_language')) {
            MLT::setActiveLanguage($lang);
            $this->route->redirect($this->route->get('full_url'));
        }
        $this->view->active_model_language = MLT::getActiveLanguage();
        $this->view->current_user 	= slACL::getCurrentUser();

        $this->view->admin_title 	= 'AEGEE';
        $this->view->revision 		= 2;
    }

}
