<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created Dec 17, 2009 12:29:56 PM
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Ability to track created and updated time
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class TimetrackAbility extends slModelAbility {

    public $_mixed_methods = array(
        'getCreatedAt'  => array(),
        'getUpdatedAt'  => array(),
    );

    public function setUp() {
        $this->_model->getStructure()->addColumn('_created_at', array(
            'type'      => 'datetime',
            'default'   => '#sql#NOW()'
        ));
        $this->_model->getStructure()->addColumn('_updated_at', array(
            'type'      => 'datetime',
            'default'   => '#sql#NOW()'
        ));
    }

    public function bootstrap() {
        $this->publishAction('getCreatedAt');
        $this->publishAction('getUpdatedAt');
    }

    public function getCreatedAt(&$objects, $params = null) {
        $this->requireModeSingle($objects);
        return $objects[0]->getRawValue('_created_at');
    }

    public function getUpdatedAt(&$objects, $params = null) {
        $this->requireModeSingle($objects);
        return $objects[0]->getRawValue('_updated_at');
    }

    public function preSave(&$changed, &$all) {
        if ($this->_model->isNew() && empty($changed['_created_at'])) {
            $changed['_created_at'] = '#sql#NOW()';
            $all['_created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($changed['_updated_at'])) {
            $changed['_updated_at'] = '#sql#NOW()';
            $all['_updated_at'] = date('Y-m-d H:i:s');
        }
    }

}