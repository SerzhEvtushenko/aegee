<?php
/**
 * @package 
 * @version 0.1
 * created: 02.05.2010 11:58:19
 */

Class ftlBlockForm extends ftlBlock{


    public function process($tag) {
        $tag = substr($tag, 6, -1);
        $params = $this->_compiler->parseParams($tag);
        if (isset($params[0]) && !isset($params['name'])) $params['name'] = $params['0'];
        if ($params['name'][0] == '"') $params['name'] = substr($params['name'], 1, -1);
        unset($params['0']);

        $params = array_merge($params, array(
            'method'    => 'post',
            'action'    => '',
            'id'        => $params['name'].'-form',
        ));
        $res = '<form method="' . $params['method'].'" enctype="multipart/form-data" action="'.$params['action'].'" name="'.$params['name'].'" id="'.$params['id'] . '"';
        if (isset($params['class'])) $res .= ' class="' . $params['class'] . '"';
        $res .= '>';
        $csrf_key = md5(time());
        $_SESSION['csrf'][$params['name']] = $csrf_key;
        $res .= '<input type="hidden" name="'.$csrf_key.'" value="easy" />';

        return $res;
    }

    public function processEnd($tag) {
        return '</form>';
    }

}