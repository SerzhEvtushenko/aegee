<?php

Class ProfileController extends slController {

    public function actionDefault(){
        $this->redirectIndexIfNotLoggedIn();
	    $user = AclUser::get();

	    if ($user['status'] == AclUser::STATUS_FAKE) {
		    $this->route->redirectIndex();
	    }

	    if (isset($_SESSION['newUser'])) {
		    unset($_SESSION['newUser']);
		    $this->view->newUser = true;
	    }

        $this->view->user = $user;
    }

    public function actionSaveAvatar(){
        $result = AclUser::saveAvatar($this->route->getVar('data'));
        $this->echoJSON($result);
    }

    public function actionRegistration() {
        if (slACL::isLoggedIn()) {
	        if (AclUser::get('status') > 0){
		        $this->route->redirect('profile');
	        }

        }

        if (isset($_SESSION['current_user_id'])) {
            $user = AclUser::loadOne(C::create()->where(array('id'=>$_SESSION['current_user_id'])));
        } else {
	        AclUser::checkForDraft();
            $user = new AclUser();
	        $user->first_name   = 'First name';
	        $user->last_name    = 'Last name';
	        $user->email        = 'email';
	        $user->phone        = 'phone';
            $user->save(false, false);
            $_SESSION['current_user_id'] = $user->id;
        }

        $this->view->user = $user;
    }

    public function actionSaveNewUser(){
        $result = false;
        if ($data = $this->route->getVar('data')){
            $result = AclUser::registration($data);

        }
        $this->echoJSON($result);
    }

    public function actionUpdateUserInfo(){
        $result = false;
        if ($data = $this->route->getVar('data')){
            $result = AclUser::updateInfo($data);

        }
        $this->echoJSON($result);
    }

    public function actionChangePassword(){
        $this->redirectIndexIfNotLoggedIn();
        if ($data = $this->route->getVar('data')) {
            $this->view->result = AclUser::changePassword($data);
        }
    }

    public function actionLogin(){
        $result = false;

        if ($data = $this->route->getVar('data')){
            if (AclUser::isValidLoginData($data)) {
                slACL::authorize(array('email' => $data['email']));
                $result = true;
            }
        }
        $this->echoJSON($result);
    }

    public function actionLogout(){
        slACL::logout();

        if (isset($_SERVER['HTTP_REFERER'])){
            $this->route->redirectReferer();
        }else{
            $this->route->redirectIndex();
        }
    }

    public function actionPasswordRecovery(){
        $result = false;

        if(!slACL::isLoggedIn() && ($email = $this->route->getVar('email'))){
            $result = AclUser::recoveryPassword($email);
        }
       $this->echoJSON($result);
    }

    public function actionPasswordChange(){

        if (slACL::isLoggedIn()) {
            $this->route->redirectIndex();
        }

        if ($hash = $this->route->getVar('hash')) {
            $this->view->setTemplate('profile/password_change.tpl');

            $id_user = Q::create('acl_users')
                ->select('id')
                ->where(array('hash' => $hash))
                ->useValue('id')
                ->one()
                ->exec();

            if (empty($id_user)) {
                throw new slRouteNotFoundException('');
            }

            $result = array('errors'=>array());

            if($data = $this->route->getVar('data')){
                $data['id'] = $id_user;

                $result = AclUser::changePassword($data);
                if ($result['status']) {
                    $this->route->redirectIndex();
                }
            }

            $this->view->result = $result;
            $this->view->errors = $result['errors'];

        } else {
            throw new slRouteNotFoundException('');
        }

    }


	public function actionDetail(){
		if ($id = $this->route->getVar('id')) {
			$bordMember = Board::loadOne(array('id'=>$id));

			if (!$bordMember) {
				throw new slRouteNotFoundException('');
			}

			$this->view->bordMember = $bordMember;
		} else {
			throw new slRouteNotFoundException('');
		}
	}

}