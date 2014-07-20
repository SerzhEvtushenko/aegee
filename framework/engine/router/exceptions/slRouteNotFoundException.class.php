<?php
/**
 * @package SolveProject
 * @subpackage Router
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 14.03.2010 11:27:32
 */

/**
 * Route Not Found exception
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
 
 Class slRouteNotFoundException extends slRouteException {
     
    public function postAction($message, $code) {
        /**
         * check if PageNotFoundController exists - post-route user to it
         *      else if dev_mode is off - redirect to index
         */
        if (class_exists('PageNotFoundController')) {
            header('HTTP/1.0 404 Not Found');
            slRouter::getCurrentRoute()->forward('page_not_found');
            slView::getInstance()->assign('not_found_message', $message);
            slView::getInstance()->assign('active_language' , MLT::getActiveLanguage());
            SL::getApplication()->render();
            die();
        } else {
            if (!SL::getProjectConfig('dev_mode')) {
                slRouter::getCurrentRoute()->redirectIndex();
            }
        }
    }
 }