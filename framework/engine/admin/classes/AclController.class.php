<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 05.06.12 12:55
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class AclController extends slController {

    public function actionAuthorizeFrontend() {
        $result = array(
            'res'   => false
        );
        if ($id = $this->route->getVar('id')) {
            slACL::initialize('frontend');
            if (slACL::getCurrentUser('id') !== 'id') {
                if (slACL::authorize(array('id'=>$id))) {
                    $result['res'] = true;
                    $result['detail'] = slACL::getCurrentUser();
                }
            }
            slACL::initialize('admin');
        }
        echo json_encode($result);
        die();
    }

}
