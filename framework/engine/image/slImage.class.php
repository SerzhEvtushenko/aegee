<?php
/**
 * @package SolveProject
 * @subpackage Image
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: Dec 13, 2009 1:22:04 PM
 */

/**
 * Work with images
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slImage {

    /**
     * @var array Engines instances
     */
    static private $_engines        = array();

    /**
     * @var slImageEngine active engine
     */
    static private $_active_engine  = null;


    static public function initialize() {
        $engine = SL::getProjectConfig('image_engine', 'slGD');
        $engine_class = $engine.'ImageEngine';
        self::$_engines[$engine] = new $engine_class;
        self::$_active_engine = self::$_engines[$engine]; 
    }


    static public function fitIn($source, $destination, $dimensions, $gravity = 'north') {
        self::$_active_engine->fitIn($source, $destination, $dimensions, $gravity);
    }

    static public function fitOut($source, $destination, $dimensions, $gravity = 'north') {
        self::$_active_engine->fitOut($source, $destination, $dimensions, $gravity);
    }

    static public function fitInFull($source, $destination, $dimensions, $gravity = 'north') {
        self::$_active_engine->fitInFull($source, $destination, $dimensions, $gravity);
    }

    static public function getImageInfo($path) {
        return self::$_active_engine->getImageInfo($path);
    }
}