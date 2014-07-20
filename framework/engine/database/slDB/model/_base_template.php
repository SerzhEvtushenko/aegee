<?php

/**
 * Class __NAME__ Generated with slDBOperator
 *
 * @package __PROJECT__
 * @version 1.0
 *
 * @copyright Solve Project, Alexandr Viniychuk
 * created __DATE__
 *
__PROPERTIES__
 */
class __BASENAME__ extends slModel {

    /**
     * @var array current structure updated from YML structure
     */
    protected $_structure = array();

    /**
     * @static
     * @param C $criteria
     * @return __NAME__
     */
    static public function loadOne($criteria = null, $query_part = null) {
        if (func_num_args() == 0) {
            return slModel::getModel("__NAME__")->_loadOne();
        } else {
            return slModel::getModel("__NAME__")->_loadOne($criteria, $query_part);
        }
    }

    /**
     * @static
     * @param C $criteria
     * @return slModelCollection
     */
    static public function loadList($criteria = null, $query_part = null) {
        if (func_num_args() == 0) {
            return slModel::getModel("__NAME__")->_loadList();
        } else {
            return slModel::getModel("__NAME__")->_loadList($criteria, $query_part);
        }
    }
}
