<?php

/**
 * slModel AclUser Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 12.08.2013 00:14:44
 */
class AclUser extends BaseAclUser {

    const STATUS_FAKE       = 0;
    const STATUS_ACTIVE     = 1;
    const STATUS_BLOCKED    = 2;

    static public function initialize(){
        if (slACL::isLoggedIn()) {
            $user   = AclUser::loadOne(slACL::getCurrentUser('id'));
            if (empty($user)) {
                slACL::logout();
                slACL::set('user', null);
            }else{
                slACL::set('user', $user->toArray());
            }
        }
    }

    static public function get($key = null){
        return slACL::getCurrentUser($key);
    }

    static public function set($key, $value){
        slACL::set('user/'.$key, $value);
    }

	static public function checkForDraft(){
		Q::create('acl_users')
				->delete()
				->where('status = ' . self::STATUS_FAKE)
				->andWhere('register_date < \'' . date('Y-m-d',(strtotime ( '-1 day' ) )).'\'')
				->exec();
	}

	static public function saveAvatar(){
		$id_user = (slACL::isLoggedIn()) ? self::get('id') : $_SESSION['current_user_id'];

		$user = self::loadOne(C::create()->where(array('id' => $id_user)));

		if ($user){

			$user->save(false, false);
			$user->loadFiles();

			slACL::set('user',$user->toArray());

			$avatar = isset($user->avatar['sizes']['small']['link']) ? $user->avatar['sizes']['small']['link'] : 'images/default_avatar.png';
		} else {
			$avatar = 'images/default_avatar.png';
		}

		return $avatar;
	}

	static public function loadList($criteria = null, $query_part = null) {
		$criteria = !empty($criteria) ? $criteria : C::create()->orderBy('first_name ASC');

		return parent::loadList($criteria, $query_part);
	}

    static public function getRightsList() {
        return slACL::getRightsList();
    }

	static public function isValidLoginData($data){
		foreach($data as $key=>$value){
			$data[$key] = trim(strip_tags($value));
		}

		if (empty($data['email']) || empty($data['password'])) {
			return false;
		}

		$user = Q::create('acl_users')
			->where(array(
				'email'     => $data['email'],
				'password'  => md5($data['password'])
			))
			->one()
			->exec();

		return (bool)$user;
	}

	static public function registration($data) {

		$result['status']   = false;
		$user_info          = array();
		$required_fields    = array(
			'first_name', 'last_name', 'email', 'phone','pass', 're_pass',
			'birthday', 'university', 'speciality'
		);

		$available_fields   = array(
			'first_name', 'last_name', 'email', 'phone', 'pass', 're_pass',
			'aegee_card','post_address', 'university', 'speciality', 'sex',
			'birthday','enable_subscription','interests','why_join','how_learned',
			'work_place',  'work_position','other_experience',
			'describe_yourself','know_more','like_to_visit','facebook_id','vk_id'
		);

		foreach($data as $key=>$value){
			if (in_array($key, $available_fields)) {
				$user_info[$key] = trim(strip_tags($value));
				if ((!strlen($user_info[$key]) && in_array($key, $required_fields))) {
					$result['errors'][$key] = true;
				}
			}
		}

		if (!empty($user_info['phone'])) {

			if (!preg_match('#^\(\d{3}\) \d{3}-\d{2}-\d{2}$#', $user_info['phone'])) {
				$result['errors']['phone'] = true;
			} else {
				$user_info['phone'] = str_replace(array("(",")","-", " "), array('',''), $user_info['phone']);
			}
		}

		if (!preg_match('#([a-zA-Z]|[0-9])+#', $user_info['pass'])) {
			$result['errors']['pass'] = true;
		}
		if ($user_info['pass'] != $user_info['re_pass']){
			$result['errors']['re_pass'] = true;
		}
		if (!self::checkEmail($user_info['email'])) {
			$result['errors']['email'] = true;
		}

		$isset_user = Q::create('acl_users')
			->select('id')
			->where('email = \''.$user_info['email'].'\'')
			->one()
			->exec();

		if (!empty($isset_user)) {
			$result['errors']['isset_email'] = true;
		}

		if (empty($result['errors']) && isset($_SESSION['current_user_id'])) {
			$user = self::loadOne(C::create()->where(array('id'=>$_SESSION['current_user_id'])));
			$user->mergeData($user_info);
			$user->status   = self::STATUS_ACTIVE;
			$user->password = md5($user_info['pass']);

			if ($user->save()) {
				slACL::authorize(array('email' => $user->email));
				self::set('user', $user->toArray());
				$result['status'] = true;
				$user->sendRegistrationMail($user_info['pass']);
				$user->subscribeUser();
				$_SESSION['newUser'] = true;
			}else {
				$result['errors'] = $user->getErrors();
			}
		}

		return $result;
	}

	static public function recoveryPassword($email){
		$result = false;
		$user   = AclUser::loadOne(C::create()->where(array('email' => $email)));
		if($user){
			$user->hash = md5(time()+$email);
			if($user->save()){
				$result = true;
				$user->sendRecoveryMail();
			}
		}else{
			$result = 'invalid_email';
		}

		return $result;

	}

	static public function changePassword($data){
		$result['status'] = false;
		$result['errors'] = array();

		foreach($data as $key=>$value){
			$data[$key] = trim(strip_tags($value));
		}

		if(strlen($data['pass']) < 1){
			$result['errors']['pass'] = true;
		}
		if(strlen($data['re_pass']) < 1){
			$result['errors']['re_pass'] = true;
		}

		if (($data['pass'] != $data['re_pass'])){
			$result['errors']['invalid_re_pass'] = true;
		}

		$user = AclUser::loadOne($data['id']);

		if($user && empty($result['errors'])){
			$user->password = $data['pass'];
			$user->hash     = '';
			if($user->save()){
				$result['status'] = true;
				slACL::authorize(array('email' => $user->email));
				$user->sendChangePasswordMail($data['pass']);
			}
		}

		return $result;
	}

	static public function checkEmail($email){
		$regex = '#^[\w-]+(?:\.[\w-]+)*@(?:[\w-]+\.)+[a-zA-Z]{2,7}$#';
		return (preg_match($regex, $email)) ? true : false;
	}

    static public function updateInfo($data){
        $result['status']   = false;

        $user = AclUser::loadOne(C::create()->where(array('id'=>self::get('id'))));
        foreach($data as $key=>$value){
            $data[$key] = trim(strip_tags($value));
        }

        $user->mergeData($data);
        if (!empty($data['pass'])) {
            if (md5($data['old_pass']) != $user->password) {
                $result['errors']['old_pass'] = 'invalid';
            }
            if(strlen($data['pass']) <= 0) {
                $result['errors']['re_pass'] = 'invalid';
            }
            if ($data['pass'] != $data['re_pass']) {
                $result['errors']['re_pass'] = 'invalid';
            }

        }

        if (empty($result['errors'])) {
            if ($user->save()) {
                slACL::set('user', $user->toArray());
                $result['status']   = true;
                $result['message'] = 'Successfully saved';
            }else {
                $result['errors'] = $user->getErrors();
            }
        }

        return $result;

    }

    static public function updateUserList(){
        $step = 0;

        while($members = Q::create('members m')
            ->select('m.*, p.password')
            ->leftJoin('my_aspnet_membership p', 'm.email = p.email')
	        ->where('m.id not in (select id from acl_users)')
            ->orderBy('m.id')
            ->limit($step.', 50')
            ->exec()) {

            foreach($members as $member) {
                $member['login'] = $member['user_name'];
                $member['password'] = md5($member['password']);
                unset($member['picture']);
                unset($member['user_name']);
                unset($member['title']);
                $member['title'] = $member['first_name'].' '.$member['last_name'];

	            $r = Q::create('acl_users')
		                ->select('id')
		                ->where('id = ' . $member['id'])
		                ->one()
		                ->exec();
	            if (empty($r)) {
		            Q::create('acl_users')
			            ->insert($member)
			            ->exec();
	            }

            }


            $step+= 50;
        }
    }

    static public function updateAvatars(){
        $users = Q::create('acl_users u')
                ->select('u.id, m.picture')
                ->leftJoin('members m', 'm.user_name = u.login')
                ->orderBy('id ASC')
                ->where('picture is not null')
                ->exec();

        $url = sl::getDirUpload().'aclusers/';
        foreach($users as $user){
            $dir = $user['id'][0];

            $path = $url.'/'.$dir.'/'.$user['id'].'/avatar/';

            if (!file_exists($url.'/'.$dir.'/')) {
                mkdir($url.'/'.$dir.'/');
            }


            if (!file_exists($url.'/'.$dir.'/'.$user['id'])){
                mkdir($url.'/'.$dir.'/'.$user['id']);
            }

            if (!file_exists($url.'/'.$dir.'/'.$user['id'].'/avatar')){
                mkdir($url.'/'.$dir.'/'.$user['id'].'/avatar');
            }

            @file_put_contents($path.'avatar.jpg', $user['picture']);
        }

//        die('.');

    }

    static public function getUserIP(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    static public function getDevice(){
        $mob_detect = new MobileDetect();
        $device = 'desktop';
        if($mob_detect->isMobile()){
            $device = 'mobile';
        }elseif($mob_detect->isTablet()){
            $device = 'tablet';
        }
        return $device;
    }


	public function getRights() {
		return slACL::getUserRights($this->email);
	}

	public function sendRegistrationMail($password){
		$title  = 'Registration';
		$template = 'index/_email.tpl';

		$body_content = StaticPage::loadCached('profile/registration_letter');
		$body_content = str_replace(array('%email','%password','%name'),array($this->email, $password, $this->first_name.' '.$this->last_name), nl2br($body_content));

		$body = slView::getInstance()
			->setActiveEngine('FTLEngine')
			->fetchTemplate($template, array(
				'title'         => $title,
				'body_content'  => $body_content,
				'base_url'      => slRouter::getBaseUrl()
			));

		slMailer::sendMail($this->email, $title, $body);

		$title  = 'Новий користувач';

		$body = 'На сайті зареєструвався новий користувач'
			.'<br/>Email:'.$this->email
			.'<br/>Phone: '. $this->phone
			.'<br/>Name: '. $this->first_name.' '.$this->last_name;

		slMailer::sendMail('hr@aegee.kiev.ua', $title, $body);
		slMailer::sendMail('it@aegee.com', $title, $body);
	}

	public function save($with_validation = true, $force_save = false) {
		$this->title = $this->first_name.' '.$this->last_name;

		return parent::save($with_validation, $force_save);
	}

    public function getSmallAvatar(){
        return (isset($this->avatar['link'])) ? $this->avatar['sizes']['small']['link'] :  'images/avatar_150.jpg?id='.$this->id;
    }

//Сори за гомнометод, не мой
    public function subscribeUser (){
        $email      = $this->email;
        $name       = $this->first_name;
        $surname    = $this->last_name;

        $kyiv_l_stat = '0';
        $kyiv_internal_l_stat = '0';

        $fullname = $surname.' '.$name;
        $fullname = urlencode($fullname);
        $fullname = '%3C'.$fullname.'%3E';
        $email = urlencode ($email);

        $kyiv_l = 'https://lists.aegee.org/cgi-bin/wa?LCMD=add+KYIV-L+'.$email.'+'.$fullname.'&X=4F68DF7F39AC65033E&Y=it%40aegee.kiev.ua&L=KYIV-L';

	    $ch = curl_init($kyiv_l);
	    curl_setopt ($ch, CURLOPT_COOKIE, 'WALOGIN=69744061656765652E6B6965762E7561-EACFCDCFCFC1D3C3DCDFCB-AomtS');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $flag = curl_exec($ch);

        if ($flag !== false){
	        $kyiv_l_stat = '1';
        }

        curl_close ($ch);

	    $kyiv_inernal_l = 'https://lists.aegee.org/cgi-bin/wa?LCMD=add+KYIV-INTERNAL-L+'.$email.'+'.$fullname.'&X=4F68DF7F39AC65033E&Y=it%40aegee.kiev.ua&L=KYIV-INTERNAL-L';

        $ch = curl_init($kyiv_inernal_l);
	    curl_setopt ($ch, CURLOPT_COOKIE, 'WALOGIN=69744061656765652E6B6965762E7561-EACFCDCFCFC1D3C3DCDFCB-AomtS');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $flag = curl_exec($ch);
        if ($flag !== false)
            $kyiv_internal_l_stat = '1';
        curl_close ($ch);

        return $kyiv_l_stat.$kyiv_internal_l_stat;
    }

	public function delete() {
		return true;
	}

	public function setPassword($value) {
		if (empty($value)) return $this->password;
		return md5($value);
	}

	public function setRights($new_rights) {

		$users_rights = slACL::getUserRights($this->email);

		if (count($new_rights)) {

			$revoked = array_diff($users_rights,$new_rights);
			$granted = array_diff($new_rights, $users_rights);

			slACL::revokeRights($this->email, $revoked);
			slACL::grantRights($this->email, $granted);
		} else {
			slACL::revokeRights($this->email, $users_rights);
		}
	}



	private function sendRecoveryMail(){
		$title  = 'Password recovery';
		$template = 'index/_email.tpl';

		$hash = slRouter::getBaseUrl() . 'password-change/' .$this->hash . '/';

		$body_content = 'Hello'
		.'<br/><br/>You sent a request to recover your password on the website '.slRouter::getBaseUrl().'.'
		.'<br/><br/><b>To continue, follow this <a href="'.$hash.'">link </a> and enter your new password.</b>'
		.'<br/><br/>*If you haven\'t sent a request to recover your password just ignore this email, it was probably sent by mistake.';



		$body = slView::getInstance()
			->setActiveEngine('FTLEngine')
			->fetchTemplate($template, array(
				'title'         => $title,
				'body_content'  => $body_content,
				'base_url'      => slRouter::getBaseUrl()
			));

		slMailer::sendMail($this->email, $title, $body);
	}

	private function sendChangePasswordMail($password){
		$title  = 'Password successfully recovered';
		$template = 'index/_email.tpl';

		$body_content = 'Your password has been recovered!<br/><br/>
                Your new password to access the website is '.slRouter::getBaseUrl().': ' . $password;

		$body = slView::getInstance()
			->setActiveEngine('FTLEngine')
			->fetchTemplate($template, array(
				'title'         => $title,
				'body_content'  => $body_content,
				'base_url'      => slRouter::getBaseUrl()
			));

		slMailer::sendMail($this->email, $title, $body);
	}


}

