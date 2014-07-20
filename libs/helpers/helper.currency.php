<?php

Class ftlHelperCurrency extends ftlBlock {

//    protected $_is_inline = true;

    public function process($params) {
        $res = '';
        if (isset($params[0])) {
            $res = (1 == $params['0']) ? 'грн' : 'евро';
        }

        return $res;
    }

}