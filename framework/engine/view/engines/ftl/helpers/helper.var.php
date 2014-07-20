<?php
/**
 * @package 
 * @version 0.1
 * created: 05.05.2010 2:35:38
 */
Class ftlHelperVar extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {
//        vd($params);
        $keys = array_keys($params);
        $var_name = $keys[0];
        $var_value = $params[$keys[0]];
        $last = '';
        foreach($params as $key=>$value) {
            if ($key !== $var_name) {
                $last .= $this->_quoteString($value);
            }
        }
        $var_value = $this->_quoteString($var_value . $last);

        return '<?php $__lv[\''.$var_name.'\'] = '.$var_value . ' ?>';
    }

    private function _quoteString($tag) {
        if (mb_strpos($tag, '$__lv') === false) {
            $tag = '"' . $tag . '"';
        }
        return $tag;
    }
 
}