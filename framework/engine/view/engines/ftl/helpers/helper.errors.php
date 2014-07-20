<?php

Class ftlHelperErrors extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {
        $res = '<span class="error">';

        $field = $params[0];
        $errors = slView::getInstance()->getVar('errors');
        $e = array();
        if ($errors && isset($errors[$field])) {
            foreach($errors[$field] as $error) {
                $e[] = $error['message'];
            }
            $res .= implode($e, ',');
        }

        return $res.'</span>';
    }

}