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
 * Validate for repeat data in other field
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class RepeatValidationRule extends slValidationRule {

    public function execute($data, $params = array()) {
        $re_field = isset($params['field']) ? $params['field'] : $params['field_name'].'_re';

        $re_data = slRouter::getCurrentRoute()->getVar($re_field);
        return ($data === $re_data);
    }

    public function getError($field, $params = array()) {
        return isset($params['error']) ? $params['error'] : 'Fields for '.$field.' have to match';
    }

}