<?php
/**
 * @package SolveProject
 * @subpackage Database
 * created 27.07.11 15:08
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

/**
 * Operate with Models as Factory
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slModelWorker {

    /**
     * @var null Cache handler
     */
    static $_cache = null;

    static public function initialize() {
        self::$_cache = array();
    }

    static public function getModelField($model_name, $criteria, $field) {

    }

    static public function getModelObject($model_name) {
        $model_class_name = slInflector::camelCase($model_name);
        if (class_exists($model_class_name)) {
            if (empty(self::$_cache[$model_class_name])) {
                self::$_cache[$model_class_name] = new $model_class_name;
            }
            return self::$_cache[$model_class_name];
        } else {
            return null;
        }
    }


}