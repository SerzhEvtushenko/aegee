<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 20.10.2009 22:40:12
 */

/**
 * Controller
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
class slController {

    public $render      = true;

    /**
     * @var slRoute $route current route of application
     */
    public $route;

    /**
     * @var slView Using for manipulate with slView in Actions
     */
    public $view;

    /**
     * Create controller action with current route
     * @param slRoute $route
     */
    public function __construct(slRoute $route) {
        $this->route = $route;
        $this->view = slView::getInstance();
    }

    /**
     * @return slView current view
     */
    public function getView() {
        return $this->view;
    }

    /**
     * @return slRoute current route
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * Magic call execute action methods and throw exception if it's invalid route
     * @param $method
     * @param $parameters
     * @throws slRouteNotFoundException
     */
    public function __call($method, $parameters) {
        if (substr($method, 0, 6) == 'action') {
            throw new slRouteNotFoundException($method);
        }
    }

    public function redirectIndexIfNotLoggedIn(){
        if (!slACL::isLoggedIn()) {
            $this->route->redirectIndex();
        }
    }

    public function echoJSON($result){
        echo json_encode($result);
        die('');
    }
}
?>
