<?php

Class ftlBlockAclRequireRight extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {
        $res = '';
        $right = trim(substr($params, strpos($params, ' '), -1));
        $res = '<?php if (slACL::hasUserRight("'.$right.'")) : ?>';
        return $res;
    }

    public function processEnd($tag) {
        return '<?php endif; ?>';
    }

}