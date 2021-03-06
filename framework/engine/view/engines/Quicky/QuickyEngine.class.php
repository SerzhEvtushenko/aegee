<?php
/**
 * Description...
 *
 * @author mounter (mounters@gmail.com)
 * @date 24.10.2009 1:27:45
 */

class QuickyEngine extends slViewEngine {

    protected
        /**
         * @var Quicky
         */
        $quicky = null;

    public function __construct() {
        $this->quicky = new Quicky();
        $this->quicky->compile_dir = SL::getDirTmp() . 'templates/' . SL::getApplicationConfig('name') . '/';
        slLocator::makeWritable($this->quicky->compile_dir);
        $this->quicky->template_dir = SL::getApplicationConfig('dir') . 'templates/';
        $this->quicky->plugins_dir[] = dirname(__FILE__).'/helpers/';
        if (is_dir(SL::getDirUserLibs() . 'helpers/')) {
            $this->quicky->plugins_dir[] = SL::getDirUserLibs() . 'helpers/';
        }
    }

    public function render($template_name, $variables = array(), $params = array()) {
        if (slRouter::getCurrentRoute('format') == 'json') {
            echo json_encode($variables);
        } else {
            echo $this->fetchTemplate($template_name, $variables, $params);
        }
        return true;
    }

    public function RAW($data) {
        if (slRouter::getCurrentRoute('format') == 'json') {
            echo json_encode($data);
        } else {
            echo $data;
        }
        slView::getInstance()->setRenderType(slView::RENDER_NONE);
        return true;        
    }

    /**
     * @param  $template_name
     * @param array $variables
     * @param array $params
     * @return string Rendered template
     */
    public function fetchTemplate($template_name, $variables = array(), $params = array()) {
        foreach($variables as $var=>$value) {
            $this->quicky->assign($var, $value);
        }
        return $this->quicky->fetch($template_name, null, null, FALSE);
    }

    public function setTemplateDir($dir) {
        $this->quicky->template_dir = $dir;
    }

}
