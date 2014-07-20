<?php
/**
 * @package
 * @version 0.1
 * created: 04.05.2010 02:58:19
 */

Class ftlHelperDefault extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {
        if ($params[1][0] != '$') $params[1] = '"'.$params[1].'"';
        return '<?php echo !empty(' . $params[0] .') ? '.$params[0].' : '. $params[1] . '; ?>';
    }

}