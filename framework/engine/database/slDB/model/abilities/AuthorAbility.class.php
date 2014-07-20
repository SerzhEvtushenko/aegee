<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 20.08.12 10:20
 */
/**
 * CLASS_DESCRIPTION
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class AuthorAbility extends slModelAbility {

    public $_mixed_methods = array(
        'getAuthor'  => array(),
        'getUpdater'  => array(),
    );

    public function setUp() {
        $this->_model->getStructure()->addColumn('_id_author', array(
            'type'      => 'int(11) unsigned',
        ));
        $this->_model->getStructure()->addColumn('_id_updater', array(
            'type'      => 'int(11) unsigned',
        ));
    }

    public function bootstrap() {

    }

    public function preSave(&$changed, &$all) {
        if ($this->_model->isNew() && empty($changed['_id_author'])) {
            $changed['_id_author'] = $this->getCurrentUserId();
            $all['_id_updater'] = $changed['_id_author'];
        }
        if (!isset($changed['_id_updater'])) {
            $changed['_id_updater'] = $this->getCurrentUserId();
        }
    }

    protected function getCurrentUserId() {
        $res = null;

        if (!is_array($this->_params)) {
            $this->_params = array('method'=>'ACL');
        }

        if ($this->_params['method'] == 'ACL') {
            $res = slACL::getCurrentUser('id');
        }

        return $res;
    }

    public function getAuthor() {

    }

}
