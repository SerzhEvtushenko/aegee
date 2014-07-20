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
 */
Class PhoneValidationRule extends slValidationRule {

    public function execute($data, $params = array()) {
        $operators = array('050', '066', '095', '099', '067', '096','097','098','096','097','098', '063','093','068', '092','092');
        $phone = str_replace(array('(',')','-',' '), array('','','',''), $data);
        if( (in_array(substr($phone, 0, 3), $operators)) && (10 == mb_strlen($phone)) ){
            return true;
        }
        return false;
    }

    public function getError($field, $params = array()) {
        return isset($params['error']) ? $params['error']  : 'Field '.$field.' must have correct phone';
    }

}