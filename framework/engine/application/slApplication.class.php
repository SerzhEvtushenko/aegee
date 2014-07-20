<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 21.10.2009 0:43:40
 */

/**
 * Main application representation
 * Also work as a registry for controllers
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slApplication {

    protected $_config          = array();
    protected $_controllers     = array();

    /**
     * @var slRoute slRoute instance
     */
    protected $_route           = null;
    protected $_name            = null;
    protected $_configured     = null;

    /**
     * @param $name string application name
     */
    public function __construct($name) {
        slProfiler::checkPoint('slApplication->_construct()');
        $this->_name = $name;
    }

    /**
     * Do whole life-circle of application
     *
     * @return void
     */
    public function run() {
        slProfiler::checkPoint('slApplication->run()');

        /**
         * activate dev mode from GET request
         */
        if (isset($_GET['dev_key'])) {
            if ($_GET['dev_key'] == SL::getProjectConfig('dev_key')) {
                $_SESSION['dev_mode'] = true;
            } else {
                $_SESSION['dev_mode'] = null;
            }
        }

        if (isset($_SESSION['dev_mode'])) {
            SL::setProjectConfig('dev_mode', true);
        }

        if (!$this->_configured) $this->configure();

        /**
         * resolving route
         */
        if ($this->_name == 'console') {
            try {
                $this->_route = slRouter::getInstance()->resolveRoute();
            } catch(Exception $e) {
                $this->_route = new slRoute(array('controller'=>'IndexTask', 'action'=>'actionDefault', 'format'=>'html'));
            }
        } else {
            $this->_route = slRouter::getInstance()->resolveRoute();
        }

        /**
         * executing controllers action
         */
        $this->navigate();

        /**
         * render view layer
         */
        $this->render();
    }

    /**
     * Loading controller and executes action specified in route
     * We already have route.
     * 
     * @param $route slRoute
     * @return boolean result
     */
    public function navigate(slRoute $route = null) {
        slProfiler::checkPoint('slApplication->navigate()');
        $route ? $this->_route = $route : $route = $this->_route;

        $first_trial = false;
        // check if current 'naviagte' is firts for application on current user request
        if (empty($this->_controllers[$route['controller']])) {
            $this->_controllers[$route['controller']] = new $route['controller']($route);
            $first_trial = true;
        }
        /**
         * execute StartupController action if it exists
         */
        $startup_controller = null;
        if (class_exists('StartupController')) {
            $startup_controller = new StartupController($route);
        }
        if ($startup_controller) {
            if (method_exists($startup_controller, 'preAction'.strtoupper($route['format']))) {
                $startup_controller->{'preAction'.strtoupper($route['format'])}();
            } elseif (method_exists($startup_controller, 'preAction')) {
                $startup_controller->preAction();
            }
        }
        /**
         * if t's first 'navigate' execute preAction of current controller
         */
        if ($first_trial && method_exists($this->_controllers[$route['controller']], 'preAction')) {
            $this->_controllers[$route['controller']]->preAction();
        }
        if (method_exists($this->_controllers[$route['controller']], $route['action'].strtoupper($route['format']))) {
            $this->_controllers[$route['controller']]->{$route['action'].strtoupper($route['format'])}();

            // duplicate code for executing postAction when it's JSON, etc. requests
            if ($first_trial && method_exists($this->_controllers[$route['controller']], 'postAction')) {
                $this->_controllers[$route['controller']]->postAction();
            }

            return true;
        }
        /**
         * execute resolved action of current controller
         */
        if (!method_exists($this->_controllers[$route['controller']], $route['action'])) {
            throw new slRouteNotFoundException('Resolved route has no proper action');
        } else {
            $this->_controllers[$route['controller']]->{$route['action']}();
        }

        /**
         * if t's first 'navigate' execute postAction of current controller
         */
        if ($first_trial && method_exists($this->_controllers[$route['controller']], 'postAction')) {
            $this->_controllers[$route['controller']]->postAction();
        }

        /**
         * execute postAction of StartupController if it exists
         */
        if ($startup_controller) {
            if (method_exists($startup_controller, 'postAction'.strtoupper($route['format']))) {
                $startup_controller->{'postAction'.strtoupper($route['format'])}();
            } elseif (method_exists($startup_controller, 'postAction')) {
                $startup_controller->postAction();
            }
        }

    }

    /**
     * Executing slView Rendering
     * @return void
     */
    public function render() {
        slProfiler::checkPoint('slApplication->render()');
//        if ($this->_route->isXHR()) {
//            slView::getInstance()->setRenderType(slView::RENDER_FORMATTED);
//        }
        slView::getInstance()->render();
    }

    /**
     * Configuring current applications, load config if if not loaded or specified
     *
     * @param $config boolean
     * @return slApplication current instance
     */
    public function configure($config = false) {
        slProfiler::checkPoint('slApplication->configure()');
        if ($config) SL::setApplicationConfig($config);

        slAutoloader::getInstance()->addDir(SL::getDirRoot() . 'apps/' . $this->_name, true);
        slRouter::getInstance()->registerRoutes(slRouter::loadApplicationRoutes($this->_name));
        slView::getInstance()->setLayoutTemplate(SL::getApplicationConfig('view/layout'));
        slACL::initialize();
        $this->_configured = true;
        return $this;
    }

    /**
     * Return current application name
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * echo dev mode tools if current request is not XHR
     */
    public function __destruct() {
        slProfiler::checkPoint('slApplication->_destruct');
        if (SL::getProjectConfig('dev_mode')
                && (slRouter::getCurrentMode() == slRouter::MODE_WEB)
                && slRouter::getCurrentRoute()
                && (SL::getProjectConfig('show_dev_console', true))
                && !slRouter::getCurrentRoute()->isXHR()) {
            slDebug::showDevTools();
        }
    }
}
