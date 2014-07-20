<?php
/**
 * @package 
 * @version 0.1
 * created: 05.05.2010 2:35:38
 */

/**
 * DESCRIPTION
 *
 * @package 
 * @subpackage
 * @version 0.1
 * @since 0.1
 *
 * @author Alexandr Viniychuk <mounter@forforce.com>
 * @copyright ForForce (c) 2009, Alexandr Viniychuk
 */
 
Class ftlHelperBreak extends ftlBlock {

    public function process($params) {
            return '<?php  break; ?>';
    }
 
}