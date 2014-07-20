<?php
/**
 * @package SolveProject
 * @subpackage ACL
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created Dec 23, 2009 9:00:32 AM
 */

/**
 * Access Control List class
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slACLUnauthorizedException extends slBaseException {

    public function postAction($message, $code) {
        $view = slView::getInstance();
        $template = slACL::getConfig('view/templates/login');
        $scope = slACL::getConfig('view/templates/scope');

        if ($template) {
            if ($scope == 'common') {
                $view->setTemplateDir(SL::getDirRoot() . 'common/templates/');
            } else {
                $view->setTemplateDir(SL::getDirRoot() . 'apps/'.$scope.'/templates/');
            }
        } else {
            $view->setTemplateDir(dirname(__FILE__).'/../templates/');
        }

        $view->setTemplate($template ? ($scope ? '_acl/' : '') . $template : '_unauthorized.tpl');

        if (!($render_type = slACL::getConfig('view/templates/render'))) {
            $view->setRenderType(slView::RENDER_STANDALONE);
        } else {
            if (($render_type == 'layouted') && ($scope != 'common')) {
                $view->setLayoutTemplate('_layout.tpl');
            }
        }

        $view->render();
        die();
    }

}