<?php
/**
 * @package SolveProject
 * @subpackage Router
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 24.10.2009 20:20:54
 */

/**
 * Resolver for web console application
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slWebConsoleResolver extends slWebResolver {

    /**
     * Additional parsing for web console vars
     * @param $params
     * @param $route
     * @return array
     */
    protected function parseVars($params, $route) {
        $res = array();
        if (isset($_GET['params']) && count($_GET['params'])) {
            foreach ($_GET['params'] as $key=>$value) {
                $res[$key] = $value;
            }
            unset($_GET['params']);
        }
        if (isset($_GET['console_keys']) && count($_GET['console_keys'])) {
            $res[':options'] = $_GET['console_keys'];
            unset($_GET['console_keys']);
        }
        foreach($_GET as $key=>$value) {
            if ($key == 'params' || $key == ':options') continue;
            $res[$key] = $value;
        }
        $options = array();
        if (!empty($_SERVER['argv']) && (count($_SERVER['argv']) > 2)) {
            foreach($_SERVER['argv'] as $key=>$item) {
                if ($key < 2) continue;

                $opt = explode('=', $item);
                if (strpos($item, '--') === 0) {
                    $options[substr($opt[0], 2)] = !empty($opt[1]) ? $opt[1] : true;
                } else {
                    if (empty($opt[1])) {
                        $res[] = $opt[0];
                    } else {
                        $res[$opt[0]] = $opt[1];
                    }
                }
            }
        }
        if (count($options)) {
            $res[':options'] = $options;
        }
        return $res;
    }

}