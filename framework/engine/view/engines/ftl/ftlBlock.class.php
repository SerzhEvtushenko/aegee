<?php
/**
 * @package SolveProject
 * @subpackage FTL
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 04.05.2010 23:52:26
 */

/**
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class ftlBlock {

    /**
    * @var FTL
    */
    protected $_compiler = null;

    protected $_is_inline = false;

    public function __construct($compiler) {
        $this->_compiler = $compiler;
    }

    public function isInline() {
        return $this->_is_inline;
    }
     
}