<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created Dec 28, 2009 4:05:07 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Validate for data is not null
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class MandatoryValidationRule extends slValidationRule {

    public function execute($data, $params = array()) {
        return !empty($data);
    }

    public function getError($field, $params = array()) {
        return isset($params['error']) ? $params['error'] : 'Field '.$field.' is required';
    }

}