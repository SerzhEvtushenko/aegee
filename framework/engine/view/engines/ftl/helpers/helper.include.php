<?php
/**
 * @package
 * @version 0.1
 * created: 04.05.2010 02:58:19
 */

Class ftlHelperInclude extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {
        $res = '';
        if (isset($params['file'])) {
            if (strpos($params['file'], '$') === false) {
                $path = $this->_compiler->getCompiledPath(str_replace(array('"', "'"), "", $params['file']), array('is_common'=>!empty($params['common'])));
                $res = '<?php include \''.$path .'\'; ?>';
            } else {
                $path = $params['file'];
                if (($path[0] !== '$') && ((strpos($path, '[\'') === false) || (strpos($path, '".') !== false))) {
                    $path = '"' . $path . '"';
                }
                $res = '<?php echo $this->fetch('.$path.'); ?>';
            }
        }
        return $res;
    }

}