<?php
/**
 * @package SolveProject
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created 28.05.12 0:04
 */
/**
 * Resolver for Admin Application
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */

class slAdminResolver extends slWebResolver {

    public function buildResult($match, $route) {
        if (isset($route['controller'])) $match['controller'] = $route['controller'];
        if (isset($route['action'])) $match['action'] = $route['action'];
        if (isset($route['format'])) $match['format'] = $route['format'];

        $module_name = $this->parseModule($match);
        $controller = 'AdminController';
        $structure = sfYaml::load(file_get_contents(SL::getDirRoot() . 'apps/admin/config/structure.yml'));
        foreach ($structure['modules'] as $i => $module) {
            if (isset($module['extends'])) {
                $extending = $structure['modules'][$module['extends']];
                $module = array_merge($extending, $module);
                $structure['modules'][$i] = $module;
            }
        }

        if (class_exists($module_name . 'Controller')) {
            $controller = $module_name . 'Controller';
            $module_name = slInflector::directorize($module_name);
        } elseif (array_key_exists(slInflector::directorize($module_name), $structure['modules'])) {
            $module_name = slInflector::directorize($module_name);
            $module_type_class = 'slAdmin'.$structure['modules'][$module_name]['type'].'Controller';
            if (class_exists($module_type_class)) {
                $controller = $module_type_class;
            } else {
                throw new slRouteNotFoundException('Module '.$module_name.' not found');
            }
        } elseif ($module_name != 'Admin') {
            throw new slRouteNotFoundException('Module '.$module_name.' not found');
        }


        return array(
            'controller'    => $controller,
            'action'        => $this->parseAction($match, $route),
            'format'        => $this->parseFormat($match),
            'uri'           => slRouter::getUri(),
            'full_url'      => 'admin'.slRouter::getUri(),
            'vars'          => $this->parseVars($match, $route),
            'route_name'    => $route['route_name'],
            'module'        => $module_name
        );

    }

    protected function parseModule($match) {
        $name = empty($match['controller']) ? SL::getApplicationConfig('routing/defaultController') : $match['controller'];
        return slInflector::camelCase($name);
    }


}
