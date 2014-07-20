<?php

/**
 * Social Controller
 * Used to register users through social networks
 * @author Evtushenko Sergey
 * @version 2.2
 */
Class SocialController extends slController {

    private $_do_acl_login     = false;

    public function preAction() {
        slSocialACL::initialize();
    }

    public function actionOdnoklasniki(){
        $social = slSocial::getConfig();
        $_SESSION['id_object'] = $this->route->getVar('id_object', 0);
        header('Location: http://www.odnoklassniki.ru/oauth/authorize
                    ?client_id='.$social['ODN_APP_ID'].'
                    &scope=VALUABLE ACCESS
                    &response_type=code
                    &redirect_uri=' . slRouter::getBaseUrl() . 'social/odnoklasniki_callback/');
        die('.');
    }

    public function actionOdnoklasnikiACL(){
        $social = slSocial::getConfig();        
        header('Location: http://www.odnoklassniki.ru/oauth/authorize
                    ?client_id='.$social['ODN_APP_ID'].'
                    &scope=VALUABLE ACCESS
                    &response_type=code
                    &redirect_uri=' . slRouter::getBaseUrl() . 'social/odnoklasniki_callback_acl/');
        die('.');
    }

    public  function actionOdnoklasnikiCallbackAcl(){
        $this->_do_acl_login = true;

        $this->actionOdnoklasnikiCallback();

        $this->view->setTemplateDir(SL::getDirEngine() . 'social/templates/');
        $this->view->setTemplate('callback_acl.tpl');
    }

    public  function actionOdnoklasnikiCallback(){
        $this->view->setTemplateDir(SL::getDirEngine() . 'social/templates/');
        $this->view->setTemplate('callback.tpl');
        $this->view->setRenderType(slView::RENDER_STANDALONE);

        if (isset($_GET['code'])) {
            $social = slSocial::getConfig();
            $ub_url = ($this->_do_acl_login) ? '_acl' : '';
            $r = slRequest::sendRequest(
                'api.odnoklassniki.ru',
                'oauth/token.do?',
                array(
                    'method'    => 'post'
                ),
                80,
                array(),
                array(
                    'code'          => $_GET['code'],
                    'redirect_uri'  => slRouter::getBaseUrl() . 'social/odnoklasniki_callback' . $ub_url . '/',
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $social['ODN_APP_ID'],
                    'client_secret' => $social['ODN_APP_SECRET_KEY']
                ),
                array(),
                array(),
                array(
                    'Content-type'  => 'application/x-www-form-urlencoded'
                )

            );

            $auth = (array)json_decode($r['data']);

            $curl = curl_init('http://api.odnoklassniki.ru/fb.do?access_token=' . $auth['access_token'] . '&application_key=' . $social['ODN_APP_PUBLIC_KEY'] . '&method=users.getCurrentUser&sig=' . md5('application_key=' . $social['ODN_APP_PUBLIC_KEY'] . 'method=users.getCurrentUser' . md5($auth['access_token'] . $social['ODN_APP_SECRET_KEY'])));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $s = curl_exec($curl);
            curl_close($curl);
            $user = json_decode($s, true);

            $user_status = $this->makeUserData($user, 'od');

            $this->view->user_status        = $user_status;
            $this->view->liked_group        = $social['like_group'] ? Social::isInGroup('od') : true;
            $this->view->isset_user_data    = (isset($user['uid'])) ? true : false;
            $this->view->social_key         = 'od';
        }
    }

    public function actionFacebookAcl() {
        if(isset($_SESSION['send_request_to_fb'])){
            unset($_SESSION['send_request_to_fb']);
            $this->route->redirect('social/facebook_callback_acl/');
        }

        $social = slSocial::getConfig();
        $fb_login_url = 'http://www.facebook.com/dialog/oauth/?client_id='.$social['FB_APP_ID']
            .'&redirect_uri='.slRouter::getBaseUrl().'social/facebook_callback_acl/'
            .'&display=popup'
            .'&scope='.$social['SETTINGS_FB'];

        $_SESSION['send_request_to_fb'] = true;
        header('Location: '.$fb_login_url);
        die();
    }

    /**
     * Send request to facebook.com for authorization user
     */
    public function actionFacebook() {
        if(isset($_SESSION['send_request_to_fb'])){
            unset($_SESSION['send_request_to_fb']);
            $this->route->redirect('social/facebook_callback/');
        }
        $_SESSION['id_object'] = $this->route->getVar('id_object', 0);
        $social = slSocial::getConfig();
        $fb_login_url = 'http://www.facebook.com/dialog/oauth/?client_id='.$social['FB_APP_ID']
            .'&redirect_uri='.slRouter::getBaseUrl().'social/facebook_callback/'
            .'&display=popup'
            .'&scope='.$social['SETTINGS_FB'];

        $_SESSION['send_request_to_fb'] = true;
        header('Location: '.$fb_login_url);
        die();
    }

    public function actionFacebookCallbackAcl() {
        $this->_do_acl_login = true;

        $this->actionFacebookCallback();

        $this->view->setTemplateDir(SL::getDirEngine() . 'social/templates/');
        $this->view->setTemplate('callback_acl.tpl');
    }

	/**
	 *Parse response for facebook.com after authorize
	 * and add user data to $_Session['user_data']
	 * id this user is new create new record in the database
	 * with social_key fb
	 */
	public function actionFacebookCallback() {
        if (isset($_SESSION['send_request_to_fb'])) {
            unset($_SESSION['send_request_to_fb']);
        }
        $social = slSocial::getConfig();

        $this->view->setTemplateDir(SL::getDirEngine() . 'social/templates/');
        $this->view->setTemplate('callback.tpl');
        $this->view->setRenderType(slView::RENDER_STANDALONE);
        $user_data = 'false';
        $user_status = 'false';
        if ($code = $this->route->getGET('code')) {
            $token_get_url = 'https://graph.facebook.com/oauth/access_token?'
                .'client_id='.slSocial::getConfig('FB_APP_ID')
                .'&redirect_uri='.slRouter::getBaseUrl().'social/facebook_callback'.($this->_do_acl_login  ? '_acl' : '').'/'
                .'&client_secret='.slSocial::getConfig('FB_APP_SECRET_KEY')
                .'&code='.$code;
            $response = file_get_contents($token_get_url);
            $params = null;
            parse_str($response, $params);
            if (empty($params['access_token'])) {
                echo '<script type="text/javascript">window.close();</script>';
                die();
            }
            $session = array();
            $session['access_token']  = $params['access_token'];
            $session['expires']       = $params['expires'];

            slSocialACL::set('facebook_session', $session, 'fb');
            slSocialACL::set('expires', $session['expires'], 'fb');

			$user = SocialFacebook::getUserData('me', $session['access_token'], 'square');
            $user_status = $this->makeUserData($user, 'fb');
            $user_data = 'true';

        }
        $this->view->user_status        = $user_status;
        $this->view->liked_group        = $social['like_group'] ? Social::isInGroup('fb') : true;
        $this->view->isset_user_data    = $user_data;
        $this->view->social_key         = 'fb';

        SL::setProjectConfig('show_dev_console', false);
    }

    public function actionVkontakteAcl(){
        $social = slSocial::getConfig();
        if(isset($_SESSION['vk_auth_data'])){unset($_SESSION['vk_auth_data']);}
        $url = 'http://oauth.vk.com/authorize'
                        .'?client_id='.$social['VK_APP_ID']
                        .'&scope='.$social['SETTINGS_VK']
                        .'&redirect_uri='.slRouter::getBaseUrl().'social/vk_callback_acl/'
                        .'&response_type=code';
        header('Location:'.$url);
    }

    public function actionVkCallbackAcl() {
        $this->_do_acl_login = true;

        $this->actionVkCallback();
        $this->view->setTemplateDir(SL::getDirEngine() . 'social/templates/');
        $this->view->setTemplate('callback_acl.tpl');
    }

	/**
	 *Send request to vk.com for authorization user
	 */
	public function actionVkontakte(){
		$social = slSocial::getConfig();
		if(isset($_SESSION['vk_auth_data'])){unset($_SESSION['vk_auth_data']);}
        $_SESSION['id_object'] = $this->route->getVar('id_object', 0);
        $url = 'http://oauth.vk.com/authorize'
                        .'?client_id='.$social['VK_APP_ID']
                        .'&scope='.$social['SETTINGS_VK']
                        .'&redirect_uri='.slRouter::getBaseUrl().'social/vk_callback/'
                        .'&response_type=code';

		header('Location:'.$url);
	}

	/**
	 *Parse response for vk.com after authorize
	 * and add user data to $_Session['user_data']
	 * id this user is new create new record in the database
	 * with social_key vk
	 */
	public function actionVkCallback(){
        $this->view->setTemplateDir(SL::getDirEngine() . 'social/templates/');
        $this->view->setTemplate('callback.tpl');
        $this->view->setRenderType(slView::RENDER_STANDALONE);
        $user_data  = 'false';
        $user_status = 'false';
		$social = slSocial::getConfig();
		if($code = $this->route->getVar('code')) {
            $vk_sub_url = ($this->_do_acl_login) ? '_acl' : '';
            $url = 'https://oauth.vk.com/access_token'
                    .'?client_id='.$social['VK_APP_ID']
                    .'&client_secret='.$social['VK_APP_SECRET_KEY']
                    .'&code='.$code
                    .'&redirect_uri='.slRouter::getBaseUrl().'social/vk_callback'.$vk_sub_url.'/';
            $response = file_get_contents($url);
            $resp = json_decode($response);

			if(isset($_SESSION['vk_auth_data']) || ($resp && $resp->access_token)){
                slSocialACL::set('user/vk_auth_data', $resp, 'vk');
                slSocialACL::set('user/expires', time()+$resp->expires_in, 'vk');
				$vk = new vkapi($social['VK_APP_ID'], $social['VK_APP_SECRET_KEY']);
				$user = SocialVKontakte::getUserData($vk);
				$user_status = $this->makeUserData($user, 'vk');
			}else{
				slSocialACL::unauthorize('vk');
			}
			$user_data = 'true';
        }
        $this->view->user_status        = $user_status;
        $this->view->liked_group        = $social['like_group'] ? Social::isInGroup('vk') : true;
		$this->view->isset_user_data    = $user_data;
        $this->view->social_key         = 'vk';
	}

	/**
	 * @param $data array user data from social network
	 * @param $flag string social type
	 * @return bool
	 */
	private function makeUserData($data, $flag){
		$data = (array)$data;
        $user_data = array();

		switch($flag){
			case 'vk': $user_data['id_social'] 	    = $data['uid'];
					   $user_data['first_name'] 	= $data['first_name'];
					   $user_data['last_name'] 	    = $data['last_name'];
					   $user_data['social_key'] 	= 'vk';
                       if (isset($data['sex'])) {
                           $user_data['sex'] = (1 == $data['sex']) ? 'female' : 'male';
                       } else {
                           $user_data['sex'] = '';
                       }
                       $user_data['birthday'] 		= isset($data['bdate']) ? date('Y-m-d', strtotime($data['bdate'])) : '';
					   $user_data['avatar_link']	= $data['photo'];
					   $user_data['more_info']	    = serialize($data);
				break;
			case 'fb': $user_data['id_social'] 	    = $data['id'];
					   $user_data['first_name'] 	= $data['first_name'];
					   $user_data['last_name'] 	    = $data['last_name'];
                       $user_data['sex']			= $data['gender'];
					   $user_data['social_key'] 	= 'fb';
					   $user_data['email']		    = $data['email'];
					   $user_data['avatar_link']	= $data['avatar_link'];
                       $user_data['birthday']       = isset($data['birthday']) ? date('Y-m-d', strtotime($data['birthday'])) : '';
                       $user_data['more_info']	    = serialize($data);
                break;
            case 'od':
                        $user_data['id_social'] 	= $data['uid'];
                        $user_data['first_name'] 	= $data['first_name'];
                        $user_data['last_name'] 	= $data['last_name'];
                        $user_data['sex']           = (isset($data['gender'])) ? ($data['gender']) : '';
                        $user_data['social_key'] 	= 'od';
                        $user_data['email']		    = '';
                        $user_data['avatar_link']	= $data['pic_1'];
                        $user_data['birthday']      = isset($data['birthday']) ? date('Y-m-d', strtotime($data['birthday'])) : '';
                        $user_data['more_info']	    = serialize($data);
                break;
		}

        if(empty($user_data['id_social'])) return 'not_login';

        $user = SocialUser::loadOne(C::create()->where(array(
                                        'id_social'=>$user_data['id_social'],
                                        'social_key'=>$user_data['social_key']
                                        ))
                                    );
        $result = false;

        if (empty($user)) {
            $result = 'new';
            $user = new SocialUser();
            $user->mergeData($user_data);
            $user->hash = substr(md5(time().$user->id_social), 0, 10).'-'.$user->id_social;
            $user->save(false, true);
        } else{
            $result = 'existing';
		}

        slSocialACL::authorize($flag, $user->toArray());

        if ($this->_do_acl_login) {
            slACL::authorizeSocialUser($user);
        }

		return $result;
	}

}