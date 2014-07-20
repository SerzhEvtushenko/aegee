<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created 16.12.10 15:11:00
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
class LengthValidationRule extends slValidationRule {

    private $min = null;
    private $max = null;

    public function execute($data, $params = array()) {
        $ok = true;
        if (isset($params['min'])) {
            $this->min = $params['min'];
            if (mb_strlen($data) < $this->min) $ok = false;
        }
        if (isset($params['max'])) {
            $this->max = $params['max'];
            if (mb_strlen($data) > $this->max) $ok = false;
        }
        return $ok;
    }

    public function getError($field, $params = array()) {
        return 'Length of '.$field.' field must be '.($this->max ? 'less than '.$this->max.($this->min ? ' and' : '') : '').' '.($this->min ? ' more than '.$this->min : '').' symbols';
    }

}
