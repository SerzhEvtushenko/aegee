<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 25.10.2009 2:39:17
 */
include_once "Console_Color.class.php";

/**
 * Console view engine
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class ConsoleEngine extends slViewEngine {


    public function __construct() {
        ob_start();
    }

    public function render($template, $variables = array(), $params = array()) {
        echo $this->fetchTemplate($template, $variables, $params);
        $data = ob_get_clean();
        echo $this->outputFilter($data);
    }

    public function fetchTemplate($template, $variables = array(), $params = array()) {
        $path = SL::getApplicationConfig('dir') . 'templates/' . $template;
        return is_file($path) ? file_get_contents($path) : '';
    }

    public function outputFilter($data) {
        if (slRouter::getCurrentMode() == 'console') return $data;

        $formatter_class = 'ffViewFormat'.ucfirst(slRouter::getCurrentRoute('format'));
        if (class_exists($formatter_class)) {
            $data = call_user_func(array($formatter_class, 'outputFilter'), $data);
        }
        return $data;
    }

}
