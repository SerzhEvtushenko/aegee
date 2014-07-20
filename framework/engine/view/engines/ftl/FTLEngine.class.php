<?php
/**
 * @package SolveProject
 * @subpackage View
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 23.10.2009 10:30:17
 */

/**
 * FTL View Engine
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class FTLEngine extends slViewEngine {

    /**
     * @var FTL
     */
    protected $_handler = null;

    public function __construct() {

        $this->_handler = new FTL();

        $compile_dir    = SL::getDirTmp() . 'templates/' . SL::getApplicationConfig('name') . '/';
        $force_compile  = SL::getProjectConfig('view/force_compile');

        if ($lang = MLT::getActiveLanguage()) {
            if ($force_compile && SL::getProjectConfig('mlt/force_compile')) {
                MLT::prepareMLTTemplatesCache(SL::getApplicationConfig('dir') . 'templates');
            }
            $this->_handler->setCompileDir($compile_dir . $lang . '/');
            $this->_handler->setTemplateDir(MLT::getTemplatesCacheDir());
        } else {
            $this->_handler->setCompileDir($compile_dir);
            $this->_handler->setTemplateDir(SL::getApplicationConfig('dir') . 'templates/');
        }

        slLocator::makeWritable($this->_handler->getCompileDir());

        $this->_handler->config('force_compile', is_null($force_compile) ? SL::getProjectConfig('dev_mode') : $force_compile);
        $this->_handler->config('save_echo', SL::getProjectConfig('view/save_echo', true));

        if (is_dir(SL::getDirUserLibs() . 'helpers/')) {
            $this->_handler->addPluginsDir(SL::getDirUserLibs() . 'helpers/');
        }
    }

    public function render($template_name, $variables = array(), $params = array()) {
        if (slRouter::getCurrentRoute('format') == 'json') {
            echo json_encode($variables);
        } else {
            echo $this->fetchTemplate($template_name, $variables, $params);
            slProfiler::checkPoint('FTLEngine::fetchTemplate');
        }
        return true;
    }

    /**
     * Output raw data to the output
     * @param $data
     * @return bool
     */
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
            $this->_handler->setVar($var, $value);
        }
        return $this->_handler->fetch($template_name, null, $params);
    }

    /**
     * set template directory base path
     * @param $dir
     */
    public function setTemplateDir($dir) {
        $this->_handler->setTemplateDir($dir);
    }

    public function getHandler() {
        return $this->_handler;
    }

}