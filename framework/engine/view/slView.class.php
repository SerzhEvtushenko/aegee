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
 * Factory storage for slView Engines
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slView {

    const           RENDER_LAYOUTED     = 'view_render_layouted';
    const           RENDER_STANDALONE   = 'view_render_standalone';
    const           RENDER_NONE         = 'view_render_none';
    const           RENDER_FORMATTED    = 'view_render_format';

    protected       $_layout            = null;
    protected       $_render_mode       = slView::RENDER_STANDALONE;

    /**
     * @var array Variables will be assigned to template
     */
    protected       $_vars              = array();

    /**
     * @var array vars for each format separately
     */
    protected       $_format_vars       = array();

    /**
     * @var array messages that was set by setMessage
     */
    protected       $_messages          = array();

    /**
     * @var array errors that was set by setError
     */
    protected       $_errors            = array();

    /**
     * @var string Current template name for displaying
     */
    protected       $_template_name     = null;

    /**
     * @var array Instances of view engines
     */
    protected       $_engines           = array();

    /**
     * @var null|string current template dir
     */
    protected       $_template_dir      = null;

    /**
     * @var null active engine
     */
    protected       $_active_engine     = null;

    /**
     * @var bool is common layout used for render
     */
    protected       $_common_layout     = false;

    /**
     * @var bool is common template used for render
     */
    protected       $_common_template   = false;

    protected       $_common_dir        = false;

    /**
     * @var slView instance of current class
     */
    static protected     $_instance     = null;

    private function __construct() {
        slProfiler::checkPoint('slView->_construct()');
        $this->setActiveEngine(SL::getApplicationConfig('view/engine') ? SL::getApplicationConfig('view/engine') : 'FTLEngine');
        slProfiler::checkPoint('after set active engine');
        $this->_template_dir = SL::getApplicationConfig('dir') . 'templates/';
        $this->_common_dir = SL::getDirRoot() . 'common/templates/';

        if (!empty($_SESSION['__view']['messages'])) {
            $this->_messages = $_SESSION['__view']['messages'];
        }
    }
    /**
     * @return slView instance singleton
     */
    static public function getInstance() {
        if (!self::$_instance) self::$_instance = new slView();

        return self::$_instance;
    }

    /**
     * Set value to variable
     * @param $name
     * @param $value
     * @param null $format
     * @return slView
     */
    public function assign($name, $value, $format = null) {
        if (!$format) {
            $this->__set($name, $value);
        } else {
            $this->_format_vars[$format][$name] = $value;
        }
        return $this;
    }

    public function assignArray($val, $format = null) {
        foreach($val as $name=>$value) {
            if (!$format) {
                $this->__set($name, $value);
            } else {
                $this->_format_vars[$format][$name] = $value;
            }
        }

    }

    public function getVar($name, $format = null) {
        if (!$format) {
            return $this->__get($name);
        } else {
            return isset($this->_format_vars[$format][$name]) ? $this->_format_vars[$format][$name] : null;
        }
    }

    public function RAW($data) {
        $this->_engines[$this->_active_engine]->RAW($data);
    }

    public function __set($name, $value) {
        $this->_vars[$name] = $value;
    }

    public function __get($name) {
        $res = isset($this->_vars[$name]) ? $this->_vars[$name] : null;
        return $res;
    }

    public function render() {
        $format = slRouter::getCurrentRoute('format');
        if (slView::RENDER_NONE === $this->_render_mode) return true;

        $vars = $this->getFormatVars();
        if ($format == 'json') {
            $this->_engines[$this->_active_engine]->render($this->_layout, $vars);
            return true;
        }

        if (null == $this->_template_name) {
            $this->_template_name = $this->detectTemplate();
        }
        if (!$this->_template_name || (!is_file($this->_template_dir . $this->_template_name) && !is_file($this->_common_dir . $this->_template_name))) {
            if (($format != 'raw') && ($this->_active_engine !== 'ConsoleEngine')) {
                throw new slViewException('No template "'.$this->_template_name.'" found in ['.$this->_template_dir . $this->_template_name.'] but RenderMode '.$this->_render_mode.' specified');
            } else {
                $this->_engines[$this->_active_engine]->render($this->_template_name, $vars, array('is_common'=>$this->_common_template));
            }
        }

        $vars['_ftl'] = array(
            'route'     => slRouter::getCurrentRoute()->get(),
            'session'   => &$_SESSION,
            'get'       => &$_GET,
            'post'      => &$_POST,
            'request'   => &$_REQUEST,
            'cookie'    => &$_COOKIE,
            'server'    => &$_SERVER,
        );
        $vars['_baseUrl']  = slRouter::getBaseUrl();
        $vars['_ftl']['is_local'] = (strpos($vars['_baseUrl'], '.local') !== false);

        //slProfiler::checkPoint('Route<br/>'.dumpAsString($vars['ftl']['route']));

        if (slView::RENDER_LAYOUTED == $this->_render_mode) {
            if (SL::getApplicationConfig('view/common_layout')) {
                $this->_common_layout = SL::getApplicationConfig('view/common_layout');
            }
            if ($this->_common_layout) {
                $this->_layout = $this->_common_layout;
            } elseif (!is_file($this->_template_dir . $this->_layout))  {
                throw new slViewException('Layout template not found but RenderMode '.$this->_render_mode.' specified');
            }
            $vars['template_content'] = $this->_engines[$this->_active_engine]->fetchTemplate($this->_template_name, $vars, array('is_common'=>$this->_common_template));
            $this->_engines[$this->_active_engine]->render($this->_layout, $vars, array('is_common'=>$this->_common_layout));
        } else {
            $this->_engines[$this->_active_engine]->render($this->_template_name, $vars, array('is_common'=>$this->_common_template));
        }

        return true;
    }

    public function fetchTemplate($template, $vars = null, $engine = null) {
        if (!$engine) $engine = $this->_active_engine;
        if ($vars === null) $vars = $this->_vars;

        if (null == $template) {
            $template = $this->detectTemplate();
        }
        if (!$template || !is_file($this->_template_dir . $template)) {
                throw new slViewException('No template "'.$template.'" found but RenderMode '.$this->_render_mode.' specified');
        } else {
            return $this->_engines[$engine]->fetchTemplate($template, $vars);
        }
    }

    private function detectTemplate() {
        // getting real name of module, without suffix
        $module_name    = slInflector::directorize(substr(slRouter::getCurrentRoute('controller'), 0, -strlen(SL::getApplicationConfig('routing/controllerSuffix'))));
        $action_name    = strtolower(substr(slRouter::getCurrentRoute('action'), 6));
        $format         = slRouter::getCurrentRoute('format');
        $ext            = in_array($format, array('html', 'htm')) ? '.tpl' : '.'.$format;
        $path_array = array(
            $this->_template_dir . $module_name . '/' . $action_name . $ext,
            $this->_template_dir . $module_name . $ext,
        );
        foreach($path_array as $path) {
            if (is_file($path)) return substr($path, strlen($this->_template_dir));
        }
        
        return false;
    }


    public function registerEngine($engine_name, slViewEngine $engine) {
        if (!empty($this->_engines[$engine_name])) {
            throw new slViewException('Engine '.$engine_name.' is already registered!');
        }
        $this->_engines[$engine_name] = $engine;
    }
    
    /**
     * @param $engine_name string Name of ViewEngine to activate
     * @return slView
     */
    public function setActiveEngine($engine_name) {
        slProfiler::checkPoint('set Active Engine');
        if (class_exists($engine_name)) {
            if (empty($this->_engines[$engine_name])) {
                $this->registerEngine($engine_name, new $engine_name);
            }
            
            $this->_active_engine = $engine_name;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActiveEngine() {
        return $this->_engines[$this->_active_engine];
    }

    public function setTemplateDir($path) {
        $this->_template_dir = $path;
        $this->_engines[$this->_active_engine]->setTemplateDir($path);
        return $this;
    }

    /**
     * @param $template_name
     * @return slView current instance
     */
    public function setTemplate($template_name, $params = array()) {
        if (!empty($params['is_common'])) $this->_common_template = $template_name;

        $this->_template_name = $template_name;

        return $this;
    }

    /**
     * @param $template_name
     * @return slView current instance
     */
    public function setLayoutTemplate($template_name, $params = array()) {
        $this->_layout = $template_name;
        if (!empty($params['is_common'])) $this->_common_layout = $template_name;

        if (!$this->_layout) {
            $this->_render_mode = slView::RENDER_STANDALONE;
        } else {
            $this->_render_mode = slView::RENDER_LAYOUTED;
        }
        return $this;
    }

    /**
     * @param $render_type
     * @return slView current instance
     */
    public function setRenderType($render_type) {
        $this->_render_mode = $render_type;

        return $this;
    }


    private function getFormatVars($format = null) {
        if (!$format) $format = slRouter::getCurrentRoute('format');
        
        $vars = $this->_vars;
        if (!empty($this->_format_vars[$format])) {
            $vars = array_merge($vars, $this->_format_vars[$format]);
        }
        return $vars;
    }

    public function setMessage($message, $session = true) {
        if ($session) {
            if (!isset($_SESSION['__view']['messages'])) $_SESSION['__view']['messages'] = array();
            $_SESSION['__view']['messages'][] = $message;
        }
        $this->_messages[] = $message;
    }

    public function hasMessages() {
        return !empty($_SESSION['__view']['messages']);
    }

    public function getMessages($clear = true) {
        $res = $this->_messages;
        if ($clear) {
            $this->_messages = array();
            $_SESSION['__view']['messages'] = array();
        }
        return $res;
    }

    public function setErrors($error, $session = true) {
        if ($session) {
            if (!isset($_SESSION['__view']['errors'])) $_SESSION['__view']['errors'] = array();
            $_SESSION['__view']['errors'][] = $error;
        }
        $this->_errors[] = $error;
    }

    public function hasErrors() {
        return !empty($_SESSION['__view']['errors']);
    }

    public function getErrors($clear = true) {
        $res = $this->_errors;
        if ($clear) {
            $this->_errors = array();
            $_SESSION['__view']['errors'] = array();
        }
        return $res;
    }


}
