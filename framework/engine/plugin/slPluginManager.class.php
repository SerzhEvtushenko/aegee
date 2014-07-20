<?php
/**
 * @package SolveProject
 * @subpackage Plugin
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 28.06.11 15:12
 */

/**
 * Plugin Manager class
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slPluginManager {

    static private $_plugins = array();

    static public function initialize() {
        $plugins = SL::getProjectConfig('plugins');
        foreach($plugins as $item) {
            self::$_plugins[$item] = array();
        }
    }

    static public function processLoad() {

    }

    static public function processPreAction() {

    }
}        