<?php
/**
 * @package 
 * @version 0.1
 * created: 02.05.2010 11:58:19
 */

Class ftlBlockForeach extends ftlBlock {

    public function process($tag) {
        $res = '<?php ';

        $tag = substr($tag, 9, -1);
        $params = $this->_compiler->parseParams($tag);

        $item_name  = isset($params['item']) ? $params['item'] : 'item';

        $key_name   = isset($params['key']) ? $params['key'] : false;
        $from       = isset($params['from']) ? $params['from'] : null;
        $iteration  = isset($params['iteration']) ? $params['iteration'] : null;
        if (!$from) throw new Exception('parameter FROM missed');

        if ($from[0] == '$') {
            $var_name = substr($from, 1);
            $this->_compiler->replaceVars($from);
        }

        if (($iteration !== null) || (array_key_exists('last', $params))) {
            $res .= '$__lv[\''.$iteration.'\'] = 0;' . "\n";
        }

        $res .= 'if (!empty('.$from.')) :' . "\n";
        $count_name = substr(md5(time()), 0, 10);
        $res .= '$__lv[\'foreach_count_'.$count_name.'\'] = count('.$from.');';
        $res .= 'foreach(' . $from;
        $res .= ' as '.($key_name ? '$__lv[\''.$key_name.'\'] => ' : ''). '$__lv[\'' . $item_name.'\']):' . "\n";
        if ($iteration !== null) {
            $res .= '$__lv[\''.$iteration.'\']++;' ."\n";
        }
        if (array_key_exists('last', $params)) {
            $res .= '$__lv[\'last\'] = $__lv[\'foreach_count_'.$count_name.'\'] == $__lv[\''.$iteration.'\']';
        }

        $res .= ' ?>' . "\n";
        return $res;
    }

    public function processEnd($tag) {
        return '<?php endforeach; endif; ?>';
    }

}