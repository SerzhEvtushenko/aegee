<?php
/**
 * @package
 * @version 0.1
 * created: 02.05.2010 11:58:19
 */

Class ftlBlockFor extends ftlBlock {

    public function process($tag) {
        $res = '<?php ';

        $tag = substr($tag, 5, -1);
        $params = $this->_compiler->parseParams($tag);

        $start  = isset($params['start']) ? $params['start'] : 0;
        $loop   = isset($params['loop']) ? $params['loop'] : null;
        $value       = isset($params['value']) ? $params['value'] : null;
        $step  = isset($params['step']) ? $params['step'] : 1;
        //if (!$from) throw new Exception('parameter FROM missed');

//        if ($from[0] == '$') {
//            $var_name = substr($from, 1);
//            $this->_compiler->replaceVars($from);
//        }

//        if ($iteration !== null) {
//            $res .= '$__lv[\''.$iteration.'\'] = 0;' . "\n";
//        }

//        $res .= 'foreach(' . $from;
//        $res .= ' as '.($key_name ? '$__lv[\''.$key_name.'\'] => ' : ''). '$__lv[\'' . $item_name.'\']):' . "\n";
//        if ($iteration !== null) {
//            $res .= '$__lv[\''.$iteration.'\']++;' ."\n";
//        }


        $this->_compiler->replaceVars($start);
        
        $this->_compiler->replaceVars($loop);

        $res .= ' ?>' . "\n";
        $res = '<?php'; 
        $res .= ' for( $__lv[\''.$value.'\'] = '.$start.'; $__lv[\''.$value.'\'] < '.$loop.'; $__lv[\''.$value.'\'] += '.$step.' ): ?>';

        return $res;
    }

    public function processEnd($tag) {
        return '<?php endfor; ?>';
    }

}