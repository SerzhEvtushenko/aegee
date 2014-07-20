<?php
/**
 * @package SolveProject
 * @subpackage ACL
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 * created Dec 22, 2009 4:24:02 PM
 */

/**
 * Task for slACL
 *
 * @version 1.0
 *
 * @author Alexandr Viniychuk <alexandr@viniychuk.com>
 * @copyright Solve Project, Alexandr Viniychuk
 */
Class AclTask extends slTask {

    public function actionAuthorize() {
        if (($login = $this->route->getVar(0))) {
            if (slACL::authorize(array(slACL::getConfig('tables/login_field')=>$login))) {
                echo 'User '.$login.' authorized!';
            } else {
                echo 'Some problem occured!';
            }
        } else {
            echo "you need to specify user login";
        }
    }

    public function actionRightsList() {
        $rights = slACL::getRightsList();
        if (count($rights)) {
            echo self::printTableFromArray($rights);
        } else {
            echo "there are no rights defined in database";
        }
    }

    public function actionUsersList() {
        $users = slACL::getUsersList();
        if (count($users)) {
            echo self::printTableFromArray($users);
        } else {
            echo "there are no users defined in database\n";
        }
    }

    public function actionCreateRight() {
        $this->requireParametersCount(2);
        if (slACL::createRight($this->route->getVar(0), $this->route->getVar(1))) {
            echo $this->colorize("Right ".$this->route->getVar(0).' successfully added' . "\n");
        } else {
            echo $this->colorize("Error occured");
        }
    }

    public function actionAddUser() {
        $this->requireParametersCount(2);
        if (slACL::addUser($this->route->getVar(0), $this->route->getVar(1))) {
            echo $this->colorize("User ".$this->route->getVar(0).' successfully added' . "\n");
        } else {
            echo $this->colorize("Error occured");
        }
    }

    public function actionGrant() {
        if (slACL::grantRights($this->route->getVar(0), $this->route->getVar(1))) {
            echo $this->colorize("Rights ".$this->route->getVar(1).' successfully granted' . "\n");
        } else {
            echo "Problems were occured";
        }
    }

    public function actionUserRights() {
        $rights = slACL::getUserRights($this->route->getVar(0));
        echo $this->colorize(implode("\n", $rights));
    }

    public function actionLogout() {
        if (isset($_SESSION['slacl']['user'])) {
            unset($_SESSION['slacl']);
            echo "User logouted<br/>";
        } else {
            echo "No user was logged in<br/>";
        }
    }

    public function actionInstall() {
        $structure = sfYaml::load(dirname(__FILE__) . '/../db/structure.yml');
        $data = sfYaml::load(dirname(__FILE__) . '/../db/data.yml');
        foreach($structure as $table_name=>$info) {
            $info['table'] = $table_name;
            $sqls = array('SET FOREIGN_KEY_CHECKS=0');
            $sqls[] = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', slDBOperator::getInstance()->generateTableSQL($info));
            Q::execSQL($sqls);
        }
        foreach($data as $table=>$data) {
            Q::execSQL('truncate `'.$table.'`');
            Q::create($table)->insert($data)->exec();
        }
        echo $this->colorize('%G+ACL successfully INSTALLED' . PHP_EOL);
    }

    public function actionUninstall() {
        $structure = sfYaml::load(dirname(__FILE__) . '/../db/structure.yml');
        $data = sfYaml::load(dirname(__FILE__) . '/../db/data.yml');
        foreach($structure as $table_name=>$info) {
            Q::execSQL('DROP TABLE IF EXISTS '.$table_name);
        }
        echo $this->colorize('%G+ACL successfully UNINSTALLED' . PHP_EOL);
    }

}