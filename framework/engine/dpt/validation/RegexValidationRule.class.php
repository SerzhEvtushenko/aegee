<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created 21.02.11 12:06:00
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
class RegexValidationRule extends slValidationRule{

    public function execute($data, $params = array()) {
        return preg_match('#'.$params[0].'#',$data);
    }

}
