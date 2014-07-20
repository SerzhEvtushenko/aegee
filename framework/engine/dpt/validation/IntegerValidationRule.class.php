<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created 02.02.11 11:40:11
 *
 * @author Pavel Vodnyakov <pavel.vodnyakoff@gmail.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Validate for integer value
 *
 * @version 1.0
 *
 * @author Pavel Vodnyakov <pavel.vodnyakoff@gmail.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class IntegerValidationRule extends slValidationRule{

    public function execute($data, $params = array()) {
        if (ctype_digit($data)) {
            return true;
        } else {
            return false;
        }
    }

    public function getError($field, $params = array()) {
        return 'Field '.$field.' must contain integer value';
    }

}
