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
 * Date format process rule
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class AutoDateFormatProcessRule extends slProcessRule {

    public function execute($data, $params = array()) {
        return date('Y-m-d', strtotime(str_replace('/', '.',$data)));
    }

}