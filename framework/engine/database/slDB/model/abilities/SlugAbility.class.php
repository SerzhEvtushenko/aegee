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
 * Ability to generate user-friendly urls
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class SlugAbility extends slModelAbility {

    public $_mixed_methods = array();


    public function setUp() {
        $this->_model->getStructure()->addColumn('_slug', array(
            'type'      => 'varchar(255)'
        ));
        slDBOperator::getInstance()->updateDBFromStructure($this->_model->getStructure()->getClassName());
        $this->rebuildSlug();
    }

    public function preSave(&$changed, &$all) {
        $field = $this->_model->getStructure('abilities/slug/field', 'title');
        if (empty($changed['_slug']) && empty($all['_slug']) && !empty($changed[$field])) {
            // @todo add check for getSlug method
            $changed['_slug'] = slInflector::slugify($changed[$field]);
        }
    }

    public function rebuildSlug() {
        $objects = Q::create($this->_table)->exec();
        $field = $this->_model->getStructure('abilities/slug/field', 'title');
        foreach($objects as $object) {
            Q::create($this->_table)
                ->update(array('_slug'=>slInflector::slugify($object[$field])))
                ->where(array($this->_pk=>$object[$this->_pk]))
                ->exec();
        }
    }

}