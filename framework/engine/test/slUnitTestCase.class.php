<?php
/**
 * @package SolveProject
 * @subpackage Test
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 23.10.2009 10:30:17
 */

/**
 * Class for extending your unit tests
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slUnitTestCase {

    /**
     * @var slUnitTestResult result of executed test
     */
    private $_result = null;

    /**
     * @var array errors was given while run
     */
    private $_errors = array();

    public function __construct() {
        $this->_result = new slUnitTestResult();
    }

    public function errorHandler($error) {
    }

    public function setUp() {}

    public function tearDown() {}

    public function assertTrue($what, $message = null) {
        if (!($result = ($what === true))) {
            $result = 'expected TRUE, but '.$this->_format_inline($what).' given';
        }
        $this->_result->add($result, $message);
    }

    public function assertFalse($what, $message = null) {
        if (!($result = ($what === false))) {
            $result = 'expected FALSE, but '.$this->_format_inline($what).' given';
        }
        $this->_result->add($result, $message);
    }

    public function assertNull($what, $message = null) {
        $result = true;
        if (!is_null($what)) {
            $result = 'expected null, but '.$this->_format_inline($what).' given';
        }
        $this->_result->add($result, $message);
    }

    public function assertEmpty($what, $message = null) {
        if (!($result = (empty($what)))) {
            $result = 'expected empty, but '.$this->_format_inline($what).' given';
        }
        $this->_result->add($result, $message);
    }
    public function assertEquals($expected, $actual, $message = null) {
        if (!($result = ($expected === $actual))) {
            $result = 'expected '
                      ."\n".dumpAsString($expected)
                      ."\nbut given:"
                      ."\n".dumpAsString($actual);
        }
        $this->_result->add($result, $message);
    }

    public function assertEqualsNotDeep($expected, $actual, $message = null) {
        if (is_array($expected)) {
            if (!is_array($actual)) {
                $this->_result->add('expected '
                      ."\n".dumpAsString($expected)
                      ."\nbut given:"
                      ."\n".dumpAsString($actual), $message);
            };
            return $this->_checkArrayNotDeep($expected, $actual);
        }

    }

    private function _checkArrayNotDeep($expected, $actual) {
        if (is_array($expected)) {
            foreach($expected as $key=>$item) {
                if (!array_key_exists($key, $actual)) {
                    return false;
                }
                if (is_scalar($item) && ($item != $actual[$key])) {
                    return false;
                }
                if (is_array($item)) {
                    if (!$this->_checkArrayNotDeep($item, $actual[$key])) {
                        return false;
                    }
                }
            }
        }

    }

    public function assertContains($expected, $container, $message = null) {
        $result = false;
        if ($container instanceof SplObjectStorage) {
            $result = $container->contains($expected);
        }

        if (is_object($expected)) {
            foreach ($container as $element) {
                if ($element === $expected) {
                    $result = true;
                }
            }
        } else {
            foreach ($container as $element) {
                if ($element == $expected) {
                    $result = true;
                }
            }
        }
        if (!$result) {
            $result = dumpAsString($container)
                      . "\n".'doesn\'t contain:'
                      ."\n".dumpAsString($expected);
        }
        $this->_result->add($result, $message);
    }

    public function getResults() {
        $results = $this->_result->getAll();
        $this->_result->clear();
        return $results;

    }

    /**
     * Return string for using in verbose print
     * @param $var
     * @return string
     */
    private function _format_inline($var) {
        $res = '';
        if (is_bool($var)) {
            $res = '(bool)'.($var ? 'TRUE' : 'FALSE');
        } elseif(is_array($var)) {
            $res = '(array)Array('.count($var).')';
        } elseif(is_object($var)) {
            $res = '(object)'.get_class($var);
        } elseif(is_int($var)) {
            $res = '(int)'.$var;
        } elseif(is_string($var)) {
            $res = '(string)"'.$var.'"';
        } else {
            $res = $var;
        }
        return $res;
    }

}
