<?php
/**
 * Description...
 *
 * @author mounter (mounters@gmail.com)
 * @date 24.10.2009 1:27:45
 */

class SmartyEngine extends slViewEngine {

    protected
        /**
         * @var Smarty
         */
        $engine = null;

    public function __construct() {
        $this->engine = new Smarty();
        $this->engine->force_compile = false;
        $this->engine->compile_dir = SL::getDirTmp() . 'templates/' . SL::getApplicationConfig('name') . '/';
        slLocator::makeWritable($this->engine->compile_dir);
        
        $this->engine->template_dir = SL::getApplicationConfig('dir') . 'templates/';
        $this->engine->plugins_dir[] = dirname(__FILE__).'/helpers/';
        if (is_dir(SL::getDirUserLibs() . 'helpers/')) {
            $this->engine->plugins_dir[] = SL::getDirUserLibs() . 'helpers/';
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
            $this->engine->assign($var, $value);
        }
        return $this->engine->fetch($template_name, null, null, null, FALSE);
    }

    public function setTemplateDir($dir) {
        $this->engine->template_dir = $dir;
    }

}
