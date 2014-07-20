<?php
/**
 * @package SolveProject
 * @subpackage Validator
 * created May 25, 2010 1:35:24 AM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Validate for unique data in database
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class UniqueValidationRule extends slValidationRule {

    public function execute($data, $params = array()) {
        /**
         * @var $object slModel
         */
        $object = $this->getData();

        if (! $object instanceof slModel) return true;

        $table = $object->getStructure('table');
        $q = Q::create($table)->one()->where(array($params['field_name']=>$data));
        if (!$object->isNew()) {
            $pk_field = $object->getPKField();
            $pk_value = $object->{$pk_field};
            $q->andWhere($pk_field.' != '.$pk_value);
        }
        return !(bool)$q->exec();
    }

    public function getError($field, $params = array()) {
        return isset($params['error']) ? $params['error'] : 'Field '.$field.' value must be unique';
    }

}