<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created Dec 28, 2009 4:42:37 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Validate for data in interval
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class IntervalValidationRule extends slValidationRule {

    private $min = null;
    private $max = null;

    public function execute($data, $params = array()) {
        if (isset($params[0])) {
            $min = $params[0];
            $max = $params[1];
            $equal = !empty($params[2]);
        } else {
            $min = $params['min'];
            $max = $params['max'];
            $equal = !empty($params['equal']);
        }
        if ($min > $max) {
            $max = $min+$max;
            $min = $max - $min;
            $max = $max - $min;
        }
        $this->min = $min;
        $this->max = $max;
        return (($data >= $min) && ($data <= $max));
    }

    public function getError($field, $params = array()) {
        return isset($params['error']) ? $params['error'] : 'Value of '.$field.' field must be between '.$this->min.' & '.$this->max;
    }

}