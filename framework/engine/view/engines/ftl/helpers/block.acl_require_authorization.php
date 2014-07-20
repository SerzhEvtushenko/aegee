<?php

Class ftlBlockAclRequireAuthorization extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {
        $res = '<?php if (slACL::checkAuthorization()) : ?>';
        return $res;
    }

    public function processEnd($tag) {
        return '<?php endif; ?>';
    }

}