<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created Dec 28, 2009 12:25:26 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Default process rule. It fill result with default value, if data is empty
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class DefaultProcessRule extends slProcessRule {

    public function execute($data, $params = array()) {
        $default = $params[0];
        return empty($data) ? $default : $data;
    }

}