<?php
/**
 * @package SolveProject
 * @subpackage Router
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created: 21.10.2009 23:20:54
 */

/**
 * Basic resolver for routes
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slWebResolver {

    /**
     * @var array regular expressions for system variables in routes
     */
    private $_system_vars = array(
        'controller'    => '[-a-z0-9]+',
        'action'        => '[-a-z]+',
        'format'        => 'html|htm|rss|xml|json'
    );

    /**
     * Resolve route from
     * @param array $route the route config to test
     * @param string $uri the uri for route to test
     * @return boolean true if route match else - false
     */
    public function checkRoute($uri, $route) {

        $uri = str_replace('//', '/', $uri);

        $pattern = $this->getPattern($route);

        preg_match($pattern, $uri, $match);

        if (count($match)) {
            return $this->buildResult($match, $route);
        }
        return false;
    }

    /**
     * Format route parameters and variables
     *
     * @param $match
     * @param $route
     * @return array route parameters
     */
    protected function buildResult($match, $route) {
        if (isset($route['controller']) && empty($match['controller'])) $match['controller'] = $route['controller'];
        if (isset($route['action'])) $match['action'] = $route['action'];
        if (isset($route['format'])) $match['format'] = $route['format'];
        return array(
            'controller'    => $this->parseController($match),
            'action'        => $this->parseAction($match, $route),
            'format'        => $this->parseFormat($match),
            'uri'           => slRouter::getUri(),
            'vars'          => $this->parseVars($match, $route),
            'route_name'    => $route['route_name'],
        );
    }

    /**
     * Create correct regular expression from route pattern
     * @param $route
     * @return string
     */
    protected function getPattern($route) {
        return '#^/' . $this->replaceConstants($route['url'], $route) . '$#isU';
    }

    /**
     * Replace constants in route for futher using
     * @param $path
     * @param array $rules
     * @return mixed
     */
    protected function replaceConstants($path, $rules = array()) {

        $matches = array();
        preg_match_all('#\{(\w+)\}#is', $path, $matches);

        if ($matches[1]) {
            foreach($matches[1] as $var) {
                if (isset($this->_system_vars[$var])) {
                    $pattern = $this->_system_vars[$var];
                } else {
                    $pattern = isset($rules[$var]) ? $rules[$var] : '[-_a-z0-9]+';
                }
                $path = str_replace('{'.$var.'}', '(?P<'. $var .'>'.$pattern.')', $path);
            }
        }
        return $path;
    }

    /**
     * Format controller name
     * @param $match
     * @return string
     */
    protected function parseController($match) {
        $name = empty($match['controller']) ? SL::getApplicationConfig('routing/defaultController') : $match['controller'];
        return slInflector::camelCase($name). SL::getApplicationConfig('routing/controllerSuffix');
    }

    /**
     * Format action name
     * @param $match
     * @param $route
     * @return int|string
     */
    protected function parseAction($match, $route) {
        $action = empty($match['action']) ? 'default' : $match['action'];
        if (isset($route['forward'])) {
            foreach($route['forward'] as $act=>$fakes) {
                if (in_array($action, $fakes)) {
                    $action = $act;
                    break;
                }
            }
        }

        $action = 'action'.slInflector::camelCase($action);
        return $action;
    }

    /**
     * Format page format name
     * @param $match
     * @return string
     */
    protected function parseFormat($match) {
        return (!empty($match['format']) ? strtolower($match['format']) : 'html');
    }

    /**
     * Parse user vars in route
     * @param $match
     * @param $route
     * @return array
     */
	protected function parseVars($match, $route) {
	    $res = array();
        if (isset($route['vars'])) {
            foreach($route['vars'] as $key=>$value) {
                $res[$key] = $value;
            }
        }
		foreach ($match as $key=>$value) {
			if (is_int($key) || isset($this->_system_vars[$key])) continue;
            $res[$key] = $value;
		}
		foreach($_REQUEST as $key=>$value) {
            if ($key == 'PHPSESSID') continue;
		    $res[$key] = $value;
		}

        return $res;
	}


}
