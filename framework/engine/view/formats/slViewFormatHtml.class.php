<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 21.12.2009 9:27:58
 */

/**
 * Formatter for HTML output
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slViewFormatHtml {

    static public function outputFilter($data) {
        return str_replace("\n", '<br/>', $data);

    }

}