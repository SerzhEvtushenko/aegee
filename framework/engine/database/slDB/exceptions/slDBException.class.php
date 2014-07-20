<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 15.11.2009 14:59:08
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Exception
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slDBException extends slBaseException {

    public function postAction($message, $code) {
//        vd($code);
        if ($code == '3D000') {
            die('no db');
        }
    }

}
