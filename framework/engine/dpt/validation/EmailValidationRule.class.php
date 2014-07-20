<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created Dec 28, 2009 6:29:34 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Validate Email
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class EmailValidationRule extends slValidationRule {

    public function execute($data, $params = array()) {
        $regex = '#^[\w-]+(?:\.[\w-]+)*@(?:[\w-]+\.)+[a-zA-Z]{2,7}$#';
        if (preg_match($regex, $data)) {
            return true;
        }
        return false;
    }

    public function getError($field, $params = array()) {
        return isset($params['error']) ? $params['error']  : 'Field '.$field.' must have correct e-mail';
    }

}