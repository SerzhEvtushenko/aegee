<?php
/**
 * @package SolveProject
 * @subpackage Router
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 21.10.2009 23:20:54
 */

/**
 * Resolver for console application
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slConsoleResolver extends slWebResolver {

    /**
     * Additional parse for console parameters
     * @param $params
     * @param $route
     * @return array
     */
    protected function parseVars($params, $route) {
        $res = parent::parseVars($params, $route);
        $options = array();
        if (count($_SERVER['argv']) > 2) {
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
