<?php
/**
 * @package 
 * @version 0.1
 * created: 04.05.2010 23:51:09
 */

Class ftlBlockContinue extends ftlBlock {

    public function process($tag) {
        return '<?php continue; ?>';
    }     

}