<?php

class DbConfig extends slDevController {

    public function actionDefault() {
        if ($data = $this->route->getVar('data')) {
            SL::setDatabaseConfig('profiler', $data['profiler'] ? true : false, false);
            $this->route->redirectSelf();
        }

        $object = SL::getDatabaseConfig();
        //@todo initialize it if database.yml was deleted
        if (empty($object)) {

        }

        $active_profile = SL::getDatabaseConfig('profiles/');


        $this->view->object = $object;
    }

    public function actionProfile() {
        $profile_name = $this->route->getVar('id');
        if ($profile_name && !($profile = SL::getDatabaseConfig('profiles/'.$profile_name))) throw new slRouteNotFoundException('Profile not found!');

        if ($data = $this->route->getVar('data')) {
            foreach($data as $key=>$value) {
                SL::setDatabaseConfig('profiles/'.$profile_name.'/'.$key, $value, false);
            }

            $this->route->redirectSelf();
        }

        $this->view->profile_name = $profile_name;
        $this->view->profile = SL::getDatabaseConfig('profiles/'.$profile_name);
    }

    public function actionCheck() {
        $res = "ok";
        echo $res;
        die();
    }

}