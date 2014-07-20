<?php
/**
 * @package 
 * @version 0.1
 * created: 05.05.2010 2:35:38
 */

 
Class ftlHelperVarDump extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {
        return '<?php var_dump('.implode(',',$params).'); ?>';
    }
 
}