<?php
/**
 * @package SolveProject
 * @subpackage ACL
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created Dec 23, 2009 7:35:06 PM
 */

/**
 * Access Control List class
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slACLUnaccreditedException extends slBaseException {

    public function postAction($message, $code) {
        $view = slView::getInstance();
        $template = slACL::getConfig('view/templates/rights');
        if ($template) {
            if (slACL::getConfig('view/templates/common')) {
                $view->setTemplateDir(SL::getDirRoot() . 'common/templates/');
            }
        } else {
            $view->setTemplateDir(dirname(__FILE__).'/../templates/');
        }

        $view
            ->setTemplate($template ? $template : '_unaccredited.tpl')
            ->setRenderType(slView::RENDER_STANDALONE)
            ->assign('message', $this->getMessage())
            ->render();
        die();

    }

}