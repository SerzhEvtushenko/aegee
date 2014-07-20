<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created Dec 28, 2009 4:04:12 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Abstract class for validation rule
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
abstract Class slValidationRule {

    private $data = null;

    public function __construct($data = null) {
        $this->data = $data;
    }

    protected function getData() {
        return $this->data;
    }

    abstract public function execute($data, $params = array());

    public function getError($field, $params=array()) {
        return 'Validation error on field '.$field;
    }

}