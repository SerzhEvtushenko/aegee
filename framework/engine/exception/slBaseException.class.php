<?php
/**
 * @package SolveProject
 * @subpackage Exception
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 24.11.2009 9:07:14
 */

/**
 * Base class for all exceptions
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slBaseException extends Exception {

    /**
     * @var string used for system
     */
	protected $type = 'Base';

    /**
     * @param string $message what is wrong
     * @param int $code
     */
    public function __construct($message, $code = 0) {
        $this->type = substr(get_class($this), 0, -9);
        $this->preAction($message, $code);
        parent::__construct($message, $code);
        $this->postAction($message, $code);
    }

    public function preAction($message, $code) {}

    /**
     * Sometime you need to do something after exception is occluded
     * @param $message
     * @param $code
     */
    public function postAction($message, $code) {}

    /**
     * Custom string representation of exception
     * @return string
     */
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }



}
