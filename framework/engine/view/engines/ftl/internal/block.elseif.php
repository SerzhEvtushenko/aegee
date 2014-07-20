<?php
/**
 * @package 
 * @version 0.1
 * created: 04.05.2010 23:51:09
 */

Class ftlBlockElseif extends ftlBlock {

    public function process($tag) {
        $res = '<?php elseif (';

        $tag = substr($tag, 8, -1);
        $this->_compiler->replaceVars($tag);
        $res .= $tag;
        $res .= '): ?>';
        return $res;
    }     

}