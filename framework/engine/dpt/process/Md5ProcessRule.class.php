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
 * Genreate data's md5 hash
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class Md5ProcessRule extends slProcessRule {

    public function execute($data, $params = array()) {
        return !empty($data) ? md5($data) : '';
    }

}