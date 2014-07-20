<?php
/**
 * @package SolveProject
 * @subpackage Router
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 24.11.2009 9:18:16
 */

/**
 * Detect application and checkRoute Routes and Modes
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slRouter {

    const
        MODE_WEB                        = 'web',
        MODE_CONSOLE                    = 'console';

    protected $routes                   = array();

    /**
     * @var array contains instances of resolvers
     */
    protected $resolvers                = array();

    /**
     * @var array list of applications
     */
    protected $_applications            = array();

    /**
     * @var array parsed fixers variables
     */
    protected $_fixers_vars             = array();

    /**
     * @var $route slRoute detected route
     */
    static private  $route              = null;

    /**
     * @var $uri string part of uri after base url
     */
    static private  $uri                = null;

    /**
     * Store information about which mode is currently used. Can be slRouter::MODE_WEB || MODE_CONSOLE
     */
    static private  $current_mode       = null;

    /**
     * @var string detected application name
     */
    static private  $application_name   = null;

    /**
     * @var Router current instance
     */
    static private  $instance           = null;

    /**
     * @var null|string contains base url, ex. http://exmpale.com/
     */
    static private  $_baseUrl           = null;

    /**
     * @var bool is current application domain based instead of folder based
     */
    static private  $_is_domain_app     = false;

    static private  $_application_name  = false;

    /**
     * Resolving mode and add ProjectApplications to self registry
     */
    private function __construct() {
//        slProfiler::checkPoint('slRouter::_construct()');
        self::resolveMode();
        self::processPreurl();
        $this->registerProjectApplications();
        $this->registerCommonRoutes();

        self::$_baseUrl = isset($_SERVER['HTTP_HOST']) ? 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].'/' : null;
        if (($sub_folder = SL::getProjectConfig('sub_folder'))) {
            self::$_baseUrl .= substr($sub_folder, 1) . '/';
        }
    }

    /**
     * Initialize routers
     *
     * @static
     * @return slRouter
     */
    static public function initialize() {
        $instance = self::getInstance();
        $instance->checkCommonRoutes();
        $instance->detectApplication();
        return $instance;
    }

    /**
     * Return Instance of slRouter. Singleton.
     *
     * @return slRouter slRouter
     */
    static public function getInstance() {
        if (!self::$instance) {
            self::$instance = new slRouter();
        }
        return self::$instance;
    }

    /**
     * Check if we have to route user to some of common routes from common/routes
     */
    public function checkCommonRoutes() {
        $resolver_class = 'slWebResolver';
        $this->resolvers[$resolver_class] = new $resolver_class;

        foreach($this->routes['routes'] as $route_name=>$params) {
            $params['route_name'] = $route_name;
            if ($found = $this->resolvers[$resolver_class]->resolve(self::$uri, $params)) {
                self::$application_name = $params['application'];
                $found['baseUrl'] = self::$_baseUrl;
                self::$route = $found;
                self::$route['application_name'] = self::$application_name;
            }
        }
    }
    /**
     * Resolve current route from current request uri
     * @return mixed slRoute to navigate
     */
    public function resolveRoute() {
        /**
         * check if application already detected
         */
        if (!self::$application_name) {
            $this->detectApplication(self::$uri);
        }

        $this->processFixers();

        if (empty(self::$route) && !empty($this->routes['routes'])) {
            foreach($this->routes['routes'] as $route_name=>$params) {
                $resolver_class = SL::getApplicationConfig('routing/resolver');
                if (!$resolver_class) $resolver_class = 'sl' . ucfirst(slRouter::getCurrentMode()) . 'Resolver';

                if (!empty($params['resolver'])) $resolver_class = $params['resolver'];

                if (empty($this->resolvers[$resolver_class])) {
                    $this->resolvers[$resolver_class] = new $resolver_class;
                }
                $params['route_name'] = $route_name;
                if ($found = $this->resolvers[$resolver_class]->checkRoute(self::$uri, $params)) {
//                    vd($found);
                    /**
                     * check if detected route has proper controller and method
                     */
                    if (!class_exists($found['controller']) || !method_exists($found['controller'], $found['action'])) {
                        continue;
                    }
                    $found['baseUrl'] = self::$_baseUrl;
                    self::$route = $found;
                    self::$route['application_name'] = self::$application_name;
                    break;
                }
            }
        }
        if (empty(self::$route)) {
            if ( (mb_strlen(self::$uri)>0) && self::$uri[mb_strlen(self::$uri)-1] != '/') {
                $slashed_url = str_replace('//', '/', self::$uri . '/');
                header('location:' . substr(self::$_baseUrl, 0, -1) .'/'. MLT::getActiveLanguage(). $slashed_url);
                die();
            }

            throw new slRouteNotFoundException('No route found for uri: '.self::$uri);
        }
        self::$route = new slRoute(self::$route);
        /**
         * set fixers vars if exists
         */
        foreach($this->_fixers_vars as $k=>$v) self::$route->setVar($k, $v);
        return self::$route;
    }

    /**
     * Search for prefix in URL – sub_folder, language (en/ru/..)
     *
     * @static
     */
    static private function processPreurl() {
        if ($pre_url = SL::getProjectConfig('sub_folder',null)) {
            self::$_baseUrl .= $pre_url;
            if (strpos(self::$uri,$pre_url) !== FALSE) {
                self::$uri = substr(self::$uri, strlen($pre_url));
            }
        }
        if ($mlt = SL::getProjectConfig('mlt')) {
            if (!empty($mlt['languages'])) {
                $reg = '#^/('.implode($mlt['languages'], '|')  .')/?.*#isU';
                preg_match($reg, self::$uri, $matches);
                if (!empty($matches[0])) {
                    self::$uri = preg_replace($reg, '', self::$uri);
                    MLT::setActiveLanguage($matches[1]);
                }
            }
        }
    }

    /**
     * Check if some of fixers exists in uri, cut it and fills to $this->_fixers_vars
     */
    private function processFixers() {
        if (!empty($this->routes['fixers'])) {
            foreach($this->routes['fixers'] as $item) {
                $path = $item['url'];
                $matches = array();
                preg_match_all('#\{(\w+)\}#is', $path, $matches);

                if ($matches[1]) {
                    foreach($matches[1] as $var) {
                        $pattern = isset($item[$var]) ? $item[$var] : '[-_a-z0-9]+';
                        $path = str_replace('{'.$var.'}', '(?P<'. $var .'>'.$pattern.')', $path);
                    }
                }
                $pattern = '#' . $path . '#is';
                $fix_match = array();
                if (preg_match($pattern, self::$uri, $fix_match)) {
                    self::$uri = str_replace($fix_match[0], '', self::$uri);
                    unset($fix_match[0]);
                    foreach($fix_match as $key=>$value) {
                        if (!intval($key)) $this->_fixers_vars[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Detect which application is would be active for current route
     *
     * @throws slRouteException if no application was found for current request
     *
     * @return string slApplication name
     */
    public function detectApplication() {
        /**
         * if application already registered return it's name
         */
        if (!is_null(self::$application_name)) return self::$application_name;

        $uri = explode('/', self::$uri);
        if (count($uri) > 1 && $uri[0] == '') array_shift($uri);

        $app_name = SL::getProjectConfig('default_application');
        /**
         * check for domain application alias
         */
        $domain_app = null;
        foreach($this->_applications as $app=>$params) {
            if (!empty($params['routing']['subdomain'])) {
                if (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], $params['routing']['subdomain']. '.') !== false) {
                    $domain_app = $app;
                    self::$_is_domain_app = $app;
                    break;
                }
            }
        }
        /**
         * if application didn't detected on domain basis detect it form request uri
         */
        if (!$domain_app) {
            foreach($this->_applications as $app=>$params) {
                if (empty($params)) $params = SL::getApplicationConfig(null, $app);
                if (!empty($params['routing']['modes']) && ! (in_array(self::$current_mode, $params['routing']['modes']))) continue;

                if ($app == $uri[0]) {
                    $app_name = $app;
                    unset($uri[0]);
                    self::$uri = '/' . implode($uri, '/');
                    break;
                }
            }
        } else {
            $app_name = $domain_app;
        }
        if (($app_name != 'console') && (empty($params['routing']['modes']) || !(in_array(self::$current_mode, $params['routing']['modes'])))) {
            if (isset($this->_applications[self::$current_mode])) {
                $app_name = self::$current_mode;
            } else {
                throw new slRouteNotFoundException('No Application found for current route!');
            }
        }
        self::$_application_name = $app_name;
        SL::setActiveApplication($app_name);
        return (self::$application_name = $app_name);
    }

    /**
     * Add $routes to internal Registry of routes for searching in
     *
     * @param array $routes
     * @return void
     */
    public function registerRoutes($routes) {
        if (!is_array($routes)) {
            vd($routes, '!@#');
            throw new slRouteException('Non array routes given');
        }
        $this->routes = array_merge($this->routes, $routes);
    }

    /**
     * Addding rules from common/routes directory which used for all applications
     */
    private function registerCommonRoutes() {
        if (is_dir($common_path = SL::getDirRoot() . 'common/routes') && ($dh = opendir($common_path))) {
            while (($file = readdir($dh)) !== false) {
                if (strpos($file, '.yml') === false) continue;
                $routes = sfYaml::load($common_path . '/' . $file);
                $this->registerRoutes($routes);
            }
            closedir($dh);
        }
    }

    /**
     * Register application for detect in detectAppliction method
     *
     * @param string $application_name
     * @param array $parameters if any
     * @return void
     */
    public function registerApplication($application_name, $parameters = array()) {
        if (empty($this->_applications[$application_name])) {
            $this->_applications[$application_name] = $parameters;
        }
    }

    /**
     * Register all applications specified in config/project.yml
     *
     * @access private
     * @throw slRouteExceiption if no application found
     * @return void
     */
    private function registerProjectApplications() {
        $project_applications = slLocator::getInstance()->in(SL::getDirRoot() . 'apps')->find('*', slLocator::TYPE_DIR, slLocator::HYDRATE_NAMES);
//        if (empty($project_applications)) throw new slRouteException('No applications found!');

        $this->_applications = SL::getProjectConfig('applications', array());
        foreach($project_applications as $app_name) {
            if (!array_key_exists($app_name, $this->_applications)) {
                $this->_applications[$app_name] = array('routing' => array('modes'=>array('web')));
            }
        }

        SL::setApplicationConfig(sfYaml::load(SL::getDirEngine() . 'task/Application/config/application.yml'), null, slRouter::MODE_CONSOLE);
        SL::setApplicationConfig(SL::getDirEngine() . 'task/Application/', 'dir', slRouter::MODE_CONSOLE);

        $this->registerApplication(slRouter::MODE_CONSOLE);

        // pre-defined app name for developers tools
        $dev_app_name = 'dev_tools';
        SL::setApplicationConfig(sfYaml::load(SL::getDirEngine() . 'task/DevTools/config/application.yml'), null, $dev_app_name);
        SL::setApplicationConfig(SL::getDirEngine() . 'task/DevTools/', 'dir', $dev_app_name);

        $this->registerApplication($dev_app_name);
    }

    /**
     * Return list or specified route
     * @param string $name
     * @return array route
     */
    public function getRoutes($name = null) {
        if ($name) {
            return array_key_exists($name, $this->routes['routes']) ? $this->routes['routes'][$name] : null;
        }
        return $this->routes;
    }

    public function __toString() {
        return self::$current_mode;
    }

    /**
     * Resolving current mode and current URI to work
     *
     * @static
     * @access private static
     * @return void
     */
    static private function resolveMode() {
        self::$current_mode = self::MODE_WEB;
        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            self::$current_mode = self::MODE_CONSOLE;
        }

        if (self::$current_mode != self::MODE_CONSOLE) {
            self::$uri = str_replace('%20', ' ', $_SERVER['REQUEST_URI'] . '');
            if (!empty($_SERVER['QUERY_STRING'])) {
                self::$uri = substr(self::$uri, 0, -strlen(str_replace('%20',' ',$_SERVER['QUERY_STRING']))-1);
            }
        } else {
            self::$uri = '/'.(!empty($_SERVER['argv'][1]) ? str_replace(':', '/', $_SERVER['argv'][1]) : '');
        }
    }

    /**
     * Load application routes for resolving
     * @static
     * @param string $name
     * @return array routes
     */
    static public function loadApplicationRoutes($name) {
        $default = SL::getDirEngine() . 'defaults/'. self::getCurrentMode()  .'/routes.yml';
        $config_file = SL::getApplicationConfig('dir', $name). 'config/routes.yml';
        if (!is_file($config_file)) {
            $config_file = $default;
        }
        return sfYaml::load($config_file);
    }


    /**
     * Return current resolved route. Also create empty slRoute if current doesn't exists
     * @static
     * @param mixed $what
     * @return slRoute
     */
    static public function getCurrentRoute($what = null) {
        if (!self::$route) self::$route = new slRoute(array());

        if ($what == 'baseUrl') return self::getBaseUrl();

        return $what ? self::$route[$what] : self::$route;
    }


    static public function getCurrentApplicationName() {
        return self::$_application_name;
    }

    /**
     * Return current base url. Used in templates for base href
     * @static
     * @return null|string
     */
    static public function getBaseUrl() {
        return self::$_baseUrl;
    }

    /**
     * Return current url prefix (language or web folder)
     * @static
     * @return null|string
     */
    static public function getUrlPrefix($params = array()) {
        $app_prefix = (self::$application_name == SL::getProjectConfig('default_application'))  || self::$_is_domain_app ? '' :self::$application_name;
        $prefix = $app_prefix;

        if (($mlt = SL::getProjectConfig('mlt')) && (!SL::getApplicationConfig('mlt/disabled')) && ($lang_alias = MLT::getActiveLanguage())) {
            if (!empty($params['lang_alias'])) $lang_alias = $params['lang_alias'];
            $prefix = $lang_alias . ($app_prefix ? '/' . $app_prefix : '');
        }

        return $prefix;
    }

    /**
     * Detect which is mode application runs in
     *
     * @return slRouter::MODE_XX constant
     */
    static public function getCurrentMode() {
        if (self::$current_mode) return self::$current_mode;

        self::resolveMode();
        return self::$current_mode;
    }

    /**
     * Return current uri;
     * @static
     * @return string uri
     */
    static public function getUri() {
        return self::$uri;
    }

    static public function getCurrentUri(){
        return self::$_baseUrl . substr(self::$uri, 1);
    }

    static public function baseRedirect(){
        if ('/'.MLT::getActiveLanguage() == $_SERVER['REQUEST_URI']) {
            header("Location: http://".$_SERVER["HTTP_HOST"].'/'.MLT::getActiveLanguage().'/');
            exit;
        }

        if (strpos($_SERVER["HTTP_HOST"], "www.") !== false) {
			header("HTTP/1.0 301 Moved Permanently");
			header("Location: http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
			exit;
		}
    }
}
