<?php
/**
 * @package SolveProject
 * @subpackage Image
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: Dec 13, 2009 7:34:57 PM
 */

/**
 * Abstract image engine class
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Abstract Class slImageEngine {


    public function getImageInfo($path) {
        if (!is_file($path)) return null;
        
        $image_info = array();
        list($width, $height, $type, $attr) = getimagesize($path, $image_info);

        $info = slLocator::getFileInfo($path);
        $info['width'] = $width;
        $info['height'] = $height;
        return $info;
    }

}