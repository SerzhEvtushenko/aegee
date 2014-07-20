<?php
/**
 * @package SolveProject
 * @subpackage Router
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 25.10.2009 14:06:13
 */

/**
 * Represent route as object for more flexibility
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slRoute implements ArrayAccess {

    /**
     * @var array internal variables
     */
    private $_vars          = array();

    /**
     * @var array user variables
     */
    private $_user_vars     = array();

    /**
     * @var array contains all forwards for current user request
     */
    private $_forwards      = array();

    /**
     * @var bool is current request via XHR
     */
    private $_xhr           = false;

    /**
     * @var array contains all POST variables
     */
    private $_post          = array();

    /**
     * @var array contains all GET variables
     */
    private $_get           = array();

    /**
     * @param $params array initial parameters for route
     */
    public function __construct($params) {
        foreach($params as $key=>$value) {
            $this->_vars[$key] = $value;
        }
        if (!empty($this->_vars['vars'])) $this->_user_vars = $this->_vars['vars'];
        foreach($_GET as $key=>$value) {
            $this->_get[$key]   = $value;
        }
        foreach($_POST as $key=>$value) {
            $this->_post[$key]  = $value;
        }

        /**
         * add event listener for forward
         */
        slEventDispatcher::addEventListener(slRouteEvent::FORWARD, array($this, 'onForward'));

        if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
            || (isset($_REQUEST['XHR_REQUEST']))
            || !empty($_REQUEST['IFRAME_FORM_SENT'])) {
            $this->_xhr = true;
        }

    }

    /**
     * Forward current route ro another without browser redirect
     * @param array|string $params should contains url or array with controller and|or action
     */
    public function forward($params) {
        if (is_string($params)) {
            $params = array(
                'controller'    => slInflector::camelCase($params).'Controller',
                'action'        => 'actionDefault'
            );
        }
        /**
         * dispatch route event
         */
        slEventDispatcher::dispatchEvent(new slEvent(slRouteEvent::FORWARD, $params), $this);
    }

    /**
     * React on route event
     * @param slEvent $evt
     */
    public function onForward(slEvent $evt) {
        $this->_forwards[] = $this->_vars;
        foreach($evt as $key=>$value) {
            $this->_vars[$key] = $value;
        }
        SL::getApplication()->navigate($this);
    }

    /**
     * Return var from POST
     * @param string $what
     * @param mixed $default
     * @return mixed var from POST
     */
    public function getPOST($what = null, $default = null) {
        $res = SL::getDeepArrayValue($this->_post, $what);
        return $res == null ? $default : $res;
    }

    /**
     * Return var from GET
     * @param string $what
     * @param mixed $default
     * @return mixed var from GET
     */

    public function getGET($what = null, $default = null) {
        $res = SL::getDeepArrayValue($this->_get, $what);
        return $res == null ? $default : $res;
    }

    /**
     * Return var from user variables or GET/POST
     * @param string $what
     * @param mixed $default
     * @return mixed
     */
    public function getVar($what = null, $default = null) {
        $res = SL::getDeepArrayValue($this->_user_vars, $what);
        return $res == null ? $default : $res;
    }

    /**
     * Return data from posted form if csrf protection ok
     * @param string $name form name
     * @return mixed form value
     * @throws slRouteCSRFException
     */
    public function getForm($name) {
        $res = SL::getDeepArrayValue($this->_user_vars, $name);
        if ($res) {
            if (!empty($_SESSION['csrf'][$name]) && !array_key_exists($_SESSION['csrf'][$name], $this->_user_vars)) throw new slRouteCSRFException('CSRF Protection fault for ['.$name.']!');
        }
        return $res;
    }

    /**
     * Return all user vars
     * @return array user vars
     */
    public function getVars() {
        return $this->_user_vars;
    }

    /**
     * Set custom user var value
     * @param $key
     * @param $value
     * @return slRoute
     */
    public function setVar($key, $value) {
        $this->_user_vars[$key] = $value;
        return $this;
    }

    /**
     * Return users var count
     * @return int count
     */
    public function getVarsCount() {
        return count($this->_user_vars);
    }

    /**
     * Return is current request is XHR
     * @return bool is xhr
     */
    public function isXHR() {
        return $this->_xhr;
    }

    /**
     * Get system var for route
     * @param string $what
     * @return mixed
     */
    public function get($what = null) {
        return SL::getDeepArrayValue($this->_vars, $what);
    }

    /**
     * Set system var for route
     * @param $value
     * @param $what
     * @return slRoute
     */
    public function set($value, $what) {
        SL::setDeepArrayValue($this->_vars, $value, $what);
        return $this;
    }


    /**
     * Trying to detect model obejct from current route
     * @param string $model_name
     * @param mixed $identifier
     * @return slModel
     */
    public function getObject($model_name, $identifier = null, $where = null) {
        if (!$identifier) {
            $variants = array_keys($this->_user_vars);
            if (!empty($variants) && in_array($variants[0], array('_slug', 'id', 'title'))) {
                $identifier = $variants[0];
            }
        }
        if (!empty($this->_user_vars[$identifier])) {
            $class_name = slInflector::camelCase($model_name);
            if (class_exists($class_name)) {
                if (!$where) {
                    $where = array();
                }
                $where[$identifier] = $this->_user_vars[$identifier];
                return call_user_func(array($class_name, 'loadOne'), $where);
            }
        }
        return null;
    }

    /**
     * Return result of getObject or create new instance of specified model
     * @param string $model_name
     * @param string $identifier
     * @return slModel
     */
    public function getObjectOrCreate($model_name, $identifier = null) {
        $res = $this->getObject($model_name, $identifier);
        $class_name = slInflector::camelCase($model_name);
        return $res ? $res : new $class_name;
    }

    /**
     * Return result of getObject or throw 404 exception
     * @param string $model_name
     * @param string $identifier
     * @return slModel
     */
    public function getObjectOr404($model_name, $identifier = null, $where = null) {
        $res = $this->getObject($model_name, $identifier, $where);
        if (!$res) throw new slRouteNotFoundException('404');
        return $res;
    }
    /**
     * Redirect to Index page
     * @return void
     */
    public function redirectIndex() {
        $this->redirect('index');
        die();
    }

    /**
     * Redirect to CurrentApp Index page
     * @return void
     */
    public function redirectAppIndex() {
        $this->redirect(slRouter::getUrlPrefix() . '/');
        die();
    }

    /**
     * Redirect to Current Controller Default page
     * @return void
     */
    public function redirectDefaultAction() {
        $controller = slInflector::directorize(substr(slRouter::getCurrentRoute('controller'), 0, -10));
        $controller_url = $controller == 'index' ? '' : $controller . '/';
        $this->redirect(slRouter::getUrlPrefix() . '/' . $controller_url);
        die();
    }

    /**
     * Redirect to current location
     * @return void
     */
    public function redirectSelf() {
        $scheme = isset($_SERVER['HTTP_SCHEME']) ? $_SERVER['HTTP_SCHEME'] : 'http';
        $this->redirect($scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true);
        die();
    }

    /**
     * Send 'location' header for redirecting to $url specified
     *       $url == index // redirect to home page
     * @param string $url
     * @param bool $full_url
     * @return void
     */
    public function redirect($url, $full_url = false) {
        if ($url == 'index') {
            $url = '';
        } elseif (strlen($url) && ($url[0] == '/')) {
            $url = substr($url, 1);
        }
        $base_url = slRouter::getCurrentRoute('baseUrl');
//        $url = str_replace('//', '/', $url);
        header('location:'. ($full_url ? $url : $base_url.$url) );
        die();
    }

    /**
     * Redirect to route from routes
     * @param string $route_alias alias from route
     * @param slModel $object use for generate url with object context
     */
    public function redirectTo($route_alias, $object = null) {
        $res = slRouteHandler::getUrl($route_alias);
        header('location: '. (slRouter::getBaseUrl()) .$res);
    }

    /**
     * Return Referrer of current page
     * @return mixed|string
     */
    public function getReferer() {
        if (isset($_SERVER['HTTP_REFERER'])) {
            return str_replace('http://'.$_SERVER['HTTP_HOST'].'/','',$_SERVER['HTTP_REFERER']);
        } else {
            return substr($_SERVER['REDIRECT_URL'],1);
        }
    }

    /**
     * Redirect back to referer
     */
    public function redirectReferer() {
        $this->redirect($this->getReferer());
    }

    public function redirectBack() {

    }

    // a lot of service function for implements array iterator interface

    public function offsetGet($pos) {
        return isset($this->_vars[$pos]) ? $this->_vars[$pos] : null;
    }

    public function offsetSet($pos, $value) {
        $this->_vars[$pos] = $value;
    }

    public function offsetExists($pos) {
        return isset($this->vars[$pos]);
    }

    public function offsetUnset($pos) {
        unset($this->vars[$pos]);
    }

    public function __toString() {
        return "slroute";
    }

}
