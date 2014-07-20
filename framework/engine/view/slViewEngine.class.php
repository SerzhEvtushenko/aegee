<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 07.11.2009 20:21:18
 */

/**
 * Abstract class for all view engines
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Abstract class slViewEngine {

    /**
     * @abstract
     * @param $template
     * @param array $variables
     * @param array $params
     */
    abstract public function render($template, $variables = array(), $params = array());

    abstract public function fetchTemplate($template, $variables = array(), $params = array());

    public function setTemplateDir($dir) {}

}
