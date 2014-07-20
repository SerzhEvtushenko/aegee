<?php
/**
 * @package SolveProject
 * @subpackage ACL
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created Dec 23, 2009 12:04:05 AM
 */

/**
 * Access Control List class
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class slACL {

    static private $_config = null;

    static private $_storage = null;

    const           USER_OK             =  1;
    const           USER_NOT_FOUND      = -2;
    const           USER_DISABLED       = -3;

    static public function initialize($scope = null) {
        if (!$scope) {
            $scope = self::getConfig('scope', 'common');
        } else {
            self::reloadConfig($scope);
        }
        if (!isset($_SESSION['slacl'][$scope])) {
            $_SESSION['slacl'][$scope] = array();
        }
        self::$_storage = &$_SESSION['slacl'][$scope];
    }

    static public function reinitialize() {
        $scope = self::getConfig('scope', 'common');
        $_SESSION['slacl'][$scope] = array();
    }

    static public function set($what, $value) {
        SL::setDeepArrayValue(self::$_storage, $value, $what);
    }

    static public function get($what, $default = null) {
        $res = SL::getDeepArrayValue(self::$_storage, $what);
        return $res ? $res : $default;
    }

    /**
     * Require right to be added for current user
     * @static
     * @throws slACLUnaccreditedException
     * @param string $right identificator of right that we require
     * @return bool
     */
    static public function requireRight($right) {
        if (self::isActive() == false) return true;

        self::requireAuthorization();
        if (!self::hasUserRight($right)) {
            throw new slACLUnaccreditedException('Access denied to '.$right);
        };
    }

    /**
     * Require user to has right on access to specified object
     * @static
     * @param $object
     * @param null $id
     * @param null $method
     * @return bool
     * @throws slACLUnaccreditedException
     */
    static public function requireObjectRight($object, $id = null, $method = null) {
        $model_type = $object;
        if (is_object($object)) {
            $id = $object->getID();
            $model_type = get_class($object);
        }
        $model_type = strtolower($model_type);
        $criteria = array(
            'object_type'       => $model_type,
            'criteria'          => $id,
            'id_user'           => slACL::getCurrentUser('id')
        );
        if ($method) $criteria['id_right'] = $model_type . '_' . $method;
        $is_allowed = Q::create('acl_objects_rights')->select('count(*) cnt')->andWhere($criteria)->one()->exec();
        if (!$is_allowed['cnt']) throw new slACLUnaccreditedException('You don\'t have access to '.$model_type);
        return true;
    }

    /**
     * Require user to be authorizeds
     * @static
     * @return bool
     * @throws slACLUnauthorizedException
     */
    static public function requireAuthorization() {
        if (self::getConfig('enabled') == false) return true;
        if (self::checkAuthorization() != self::USER_OK) self::processUnauthorized();
        return true;                                                                                                                                
    }

    static private function processUnauthorized() {
        if ($config = self::getConfig('unauthorized')) {
            if (isset($config['redirect'])) {
                slRouter::getCurrentRoute()->redirect($config['redirect']);
            } elseif (isset($config['call'])) {
                call_user_func($config['call']);
            }
        }
        throw new slACLUnauthorizedException('Authorization required');
    }

    /**
     * Authorize user
     * @static
     * @param array $data
     * @return bool
     */
    static public function checkAuthorization($data = array()) {
        $res = false;
        if (self::isLoggedIn()) {
            $res = true;
        }
        if (!$data && isset($_POST['slacl_login'])) {
            $data = $_POST['slacl_login'];
        }
        if (!empty($data)) {
            $search_data = array(
                self::getConfig('tables/login_field')   => isset($data[self::getConfig('tables/login_field')]) ? $data[self::getConfig('tables/login_field')] : '',
                self::getConfig('tables/password_field')   => md5($data[self::getConfig('tables/password_field')]),
            );

            if (($auth_res = self::authorize($search_data)) > 0) {
                slRouter::getCurrentRoute()->redirectSelf();

                $res = true;
            } else {
                $message = 'Incorrect login or password';
                if ($auth_res == self::USER_DISABLED) $message = 'Account is temporary disabled';
                slView::getInstance()->assign('slacl_error', $message);
                slView::getInstance()->assign('login', $search_data[self::getConfig('tables/login_field')]);
                $res = self::USER_NOT_FOUND;
            }
        }
        return $res;
    }
           
    /**
     * Return all user rights
     * @static
     * @param $login
     * @return array
     */
    static public function getUserRights($login) {
        $rights = Q::create(self::getConfig('tables/users').' u')
            ->select('ur.id_right u_r, gr.id_right g_r')
            ->leftJoin('acl_users_rights ur', 'u.id = ur.id_user')
            ->leftJoin('acl_users_groups ug', 'u.id = ug.id_user')
            ->leftJoin('acl_groups_rights gr', 'ug.id_group = gr.id_group')
            ->where(array('u.'.self::getConfig('tables/login_field')=>$login))
            ->exec();

        $result = array();
        foreach($rights as $row) {
            if (!empty($row['u_r'])) $result[] = $row['u_r'];
            if (!empty($row['g_r'])) $result[] = $row['g_r'];
        }
        return $result;
    }

    /**
     * Check user for specified right
     * @static
     * @param $right
     * @param null $user
     * @return bool
     */
    static public function hasUserRight($right, $user = null) {
        if (!$user) {
            if (!self::isLoggedIn()) return false;
            $user = self::get('user');
        }

        if (!is_array($right)) $right = array(strtolower($right));
        foreach($right as $key=>$item) {
            if (strpos($item, '_')) {
                $base = substr($item, 0, strpos($item, '_'));
                if (!in_array($base, $right)) $right[] = $base;
            }
        }
        if (self::getConfig('enable_root')) $right[] = 'root';

        $res = Q::create(self::getConfig('tables/users').' u')
                ->select('count(*) as cnt')
                ->leftJoin('acl_users_rights ur', 'u.id = ur.id_user')
                ->leftJoin('acl_users_groups ug', 'u.id = ug.id_user')
                ->leftJoin('acl_groups_rights gr', 'ug.id_group = gr.id_group')
                ->where(array('u.id'=>$user['id']))
                ->useValue('cnt')
                ->andWhere(C::create(array('ur.id_right'=>$right))->orWhere(array('gr.id_right'=>$right)))
                ->one()
                ->exec();
        return empty($res) ? false : true;
    }

    /**
     * Return rights for group with specified ID
     * @static
     * @param $id
     * @todo implement
     */
    static public function getGroupRights($id) {
        
    }

    /**
     * Authorize user
     * @static
     * @param $data
     * @return int
     */
    static public function authorize($data) {
        if (($user = self::findUser($data))) {
            if ($af = self::getConfig('tables/active_field')) {
                if (empty($user[$af])) return self::USER_DISABLED;
            }
            self::set('user', $user);
            Q::create(self::getConfig('tables/users'))
                ->update(array('last_login'=>'#sql#NOW()'))
                ->where(array('id'=>$user['id']))
                ->exec();
            return self::USER_OK;
        } else {
            return self::USER_NOT_FOUND;
        }
    }

    /**
     * Add logged in Social User to global database and authorize it
     * @param SocialUser $user
     */
    static public function authorizeSocialUser(SocialUser $social_user) {
        $current_global_id  = Q::create(self::getConfig('tables/users'))->where(array('id_social'=>$social_user->id_social, 'social_key'=>$social_user->social_key))->useValue('id')->exec();
        if (empty($current_global_id)) {
            $user = new User();
            foreach($social_user->toArray() as $key=>$item) {
                if (in_array($key, array('id', 'password'))) continue;
                if (!empty($item)) $user[$key] = $item;
            }
            $user->save(false, false);
            $current_global_id = $user->id;
        }
        if ($current_global_id) {
            self::authorize(array('id'=>$current_global_id));
        } else {
            return self::USER_NOT_FOUND;
        }
    }

    static public function getCurrentUser($key = null) {
        if (!self::isLoggedIn()) return null;

        return $key ? self::get('user/'.$key) : self::get('user');
    }

    static public function logout() {
        self::reinitialize();
    }

    static public function findUser($data) {
        return Q::create(self::getConfig('tables/users'))->where($data)->one()->exec();
    }

    /**
     * Add user to specified in config 'tables/users' table.
     * @static
     * @param $login
     * @param $password
     * @return mixed
     */
    static public function addUser($login, $password) {
        return Q::create(self::getConfig('tables/users'))->insert(array(self::getConfig('tables/login_field')=>$login, self::getConfig('tables/password_field')=>md5($password)))->exec();
    }

    static public function createRight($id, $title) {
        return Q::create('acl_rights')->insert(array('id'=>$id, 'title'=>$title))->exec();
    }

    static public function grantRights($login, $rights) {
        if (!is_array($rights)) {
            if (strpos($rights, ',')) {
                $rights = explode(',', $rights);
            } else {
                $rights = array($rights);
            }
        }

        $result = false;
        if (($user = Q::create(self::getConfig('tables/users'))->where(array(self::getConfig('tables/login_field')=>$login))->one()->exec())) {
            $result = true;

            foreach($rights as $id_right) {
                if (($right = Q::create('acl_rights')->where(array('id'=>$id_right))->one()->exec())) {
                    Q::create('acl_users_rights')
                            ->insert(array('id_user'=>$user['id'], 'id_right'=>$right['id']))
                            ->exec();
                } else {
                    $result =false;
                }
            }
        }
        return $result;
    }

    static public function reloadConfig($scope) {
        self::$_config = sfYaml::load(dirname(__FILE__) . '/config/config.yml');
        if (is_file(SL::getDirRoot() . 'config/acl.yml')) {
            $over_config = sfYaml::load(SL::getDirRoot() . 'config/acl.yml');
            SL::extendDeepArrayValue(self::$_config, $over_config);
        }
        if (is_file(SL::getDirRoot() . $scope) . '/config/acl.yml') {
            $over_config = sfYaml::load(SL::getDirRoot() . 'apps/' . $scope . '/config/acl.yml');
            if ($over_config) SL::extendDeepArrayValue(self::$_config, $over_config);
        }

    }

    static public function getConfig($what = null, $default = null) {
        if (is_null(self::$_config)) {
            self::$_config = sfYaml::load(dirname(__FILE__) . '/config/config.yml');
            if (is_file(SL::getDirRoot() . 'config/acl.yml')) {
                $over_config = sfYaml::load(SL::getDirRoot() . 'config/acl.yml');
                SL::extendDeepArrayValue(self::$_config, $over_config);
            }
            if (is_file(SL::getDirRoot() . slRouter::getCurrentApplicationName()) . '/config/acl.yml') {
                $over_config = sfYaml::load(SL::getDirRoot() . 'apps/' . slRouter::getCurrentApplicationName() . '/config/acl.yml');
                if ($over_config) SL::extendDeepArrayValue(self::$_config, $over_config);
            }
        }
        $res = SL::getDeepArrayValue(self::$_config, $what);
        return $res ? $res : $default;
    }

    static public function isLoggedIn() {
        return (self::get('user') !== null);
    }

    static public function isActive() {
        return self::getConfig('enabled');
    }

    static public function switchOff() {
        SL::setDeepArrayValue(self::$_config, false, 'enabled');
    }

    static public function switchOn() {
        SL::setDeepArrayValue(self::$_config, true, 'enabled');
    }

    static public function getRightsList() {
        return Q::create('acl_rights')->exec();
    }

    static public function getUsersList() {
        return Q::create(self::getConfig('tables/users'))->exec();
    }

    static public function revokeRights($login, $rights) {
        if (!is_array($rights)) {
            if (strpos($rights, ',')) {
                $rights = explode(',', $rights);
            } else {
                $rights = array($rights);
            }
        }

        $result = false;
        if (($user = Q::create(self::getConfig('tables/users'))->where(array(self::getConfig('tables/login_field')=>$login))->one()->exec())) {

	        Q::create('acl_users_rights')
                ->delete()
                ->where(array('id_user'=>$user['id'], 'id_right'=>$rights))
                ->exec();
            $result = true;
        }
        return $result;
    }

}