<?php
/**
 * @package SolveProject
 * @subpackage Test
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 26.10.10 18:34
 */

/**
 * Using for RUN unit test
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slUnitTestRunner {

    private $_tests_to_run  = array();

    private $_results       = array();

    static private $_instance = null;

    /**
     * @static
     * @return slUnitTestRunner
     */
    static public function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new slUnitTestRunner();
        }
        return self::$_instance;
    }

    public function addTest($test_class, $test_file) {
        $this->_tests_to_run[] = array(
            'class' =>  $test_class,
            'file'  =>  $test_file,
        );
        return $this;
    }

    public function runAllTests() {

        foreach($this->_tests_to_run as $item) {
            if (is_file($item['file'])) {
                include_once $item['file'];
            } else {
                continue;
            }
            /**
             * @var slUnitTestCase $test_class
             */
            $test_class = new $item['class']();
            if (is_callable(array($test_class, 'setUp'))) {
                $test_class->setUp();
            }
            $methods = get_class_methods($test_class);
            $this->_results[$item['class']] = array();

            foreach($methods as $method) {
                if (strpos($method, 'test') === 0) {
                    $test_class->$method();
                    $this->_results[$item['class']][$method] = $test_class->getResults();
                }
            }

            if (is_callable(array($test_class, 'tearDown'))) {
                $test_class->tearDown();
            }
        }
        $this->clearTests();
        return $this->_results;
    }

    public function run($test_class, $test_file) {
        $this->addTest($test_class, $test_file);
        return $this->runAllTests();
    }



    public function clearTests() {
        $this->_tests_to_run = array();
    }


}
