<?php
/**
 * @package SolveProject
 * @subpackage Router
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 21.10.2009 23:20:54
 */

/**
 * Default Page Not Found Controller
 * It try to find _404.tpl in your common folder.
 * You can override it by creating your own PageNotFoundController
 * in application/conrtollers folder
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
 
 Class PageNotFoundController extends slController {

     public function actionDefault() {
		 header('HTTP/1.0 404 Not found');
		 header('status: 404 Not found');
         $this->view->setRenderType(SL::getProjectConfig('view/not_found') == 'standalone' ? slView::RENDER_STANDALONE : slView::RENDER_LAYOUTED);
         $this->view->setTemplate('_404.tpl', array('is_common'=>SL::getProjectConfig('view/common', false)));
     }
 }




