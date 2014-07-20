<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created Dec 28, 2009 12:26:36 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Abstract class for process rule
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Abstract Class slProcessRule {

    abstract public function execute($data, $params = array());

}