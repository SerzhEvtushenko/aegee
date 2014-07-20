<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 26.10.10 19:03
 */

/**
 * Result of unit test
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slUnitTestResult {

    private $_results = array();

    public function add($result, $message) {
        $this->_results[] = array(
            'result'    => $result,
            'message'   => $message
        );
    }

    public function getAll() {
        return $this->_results;
    }

    public function clear() {
        $this->_results = array();
    }

    static public function getConsoleResult($results, $verbose = false) {
        $res = 'Testing results:' . "\n";


        foreach($results as $class=>$methods) {
            $res .= '=== '.$class . ":\n";
            $total_tests_count = 0;
            $total_failed_tests = 0;

            foreach($methods as $method_name=>$method_results) {
                $method_tests_count = 0;
                $method_failed_tests = 0;

                $res .= "+ " . $method_name. ":\n";
                foreach($method_results as $key => $mr) {
                    $method_tests_count++;

                    $ok = $mr['result'] === true;
                    if ($verbose || !$ok) {
                        $res .= ($ok ? '+ ' : '- ').($key+1).'...'.($ok ? 'ok  ' : 'fail').($mr['message'] ? '   # '.$mr['message'] : '') . "\n";
                    }
                    if (!$ok) {
                        if ($mr['result']) {
                            $res .= ' '.$mr['result'] . "\n";
                        }
                        $method_failed_tests++;
                    }
                }
                $res .= ($method_failed_tests == 0 ? '%Gall passed.%n' : ($method_tests_count - $method_failed_tests) . ' from '.$method_tests_count.' passed.') . "\n";
            }
            $total_tests_count  += $method_tests_count;
            $total_failed_tests += $method_failed_tests;
        }
//        $res .= 'Total: ' . ($total_tests_count - $total_failed_tests) . ' from '.$method_tests_count.' passed.' . "\n";
        return $res. "\n";
    }

}
