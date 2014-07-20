<?php
/**
 * @package 
 * @version 0.1
 * created: 02.05.2010 11:58:19
 */

Class ftlBlockIf extends ftlBlock{


    public function process($tag) {
        $res = '<?php if (';

        $tag = substr($tag, 4, -1);
        $this->_compiler->replaceVars($tag);
        $res .= $tag;
        $res .= '): ?>';
        return $res;
    }

    public function processEnd($tag) {
        return '<?php endif; ?>';
    }

}