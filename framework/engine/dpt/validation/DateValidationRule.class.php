<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created Dec 28, 2009 6:32:02 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Validate date
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class DateValidationRule extends slValidationRule {

    public function execute($data, $params = array()) {
        if (is_string($data) && !is_numeric($data)) {
			return (boolean)strtotime($data);
		} elseif(is_numeric($data)) {
			return (boolean)$data;
		}
    }
}