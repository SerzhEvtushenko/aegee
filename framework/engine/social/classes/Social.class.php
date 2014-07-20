<?php

/**
 * Social
 * Work with social networks
 * @author Evtushenko Sergey
 * @version 1.0
 */
class Social{

	/**
	 * AddImageToAlbum
	 * @static
	 * @param $title_album string name of the album if you need to create an album
	 * @param $description_album string description of the album if you need to create an album
	 * @param $description_image string description of the image if you need to create an album
	 * @param $img_path string path to the image that will be added to the album
	 * @return bool success or fail
	 */
	static public function addImageToAlbum($title_album, $description_album, $description_image,  $img_path){
		if(!isset($_SESSION['user_data'])){return false;}
		$result = false;
		if('fb' == $_SESSION['user_data']['social_key']){
			$result = self::addImageToAlbumFb($title_album, $description_album, $description_image, $img_path);
		}elseif('vk' == $_SESSION['user_data']['social_key']){
			$result = self::addImageToAlbumVk($title_album, $description_album, $description_image, $img_path);
		}
		return $result;
	}

	/**
	 * AddImageToAlbumVk
	 * @static
	 * @param $title string
	 * @param $description string
	 * @param $img_path string
	 * @return bool success or fail
	 */
	static private  function addImageToAlbumVk($title_album, $description_album, $description_image, $img_path){
		$social = slSocial::getConfig();
		$vk = new vkapi($social['VK_APP_ID'], $social['VK_APP_PKEY']);
		$response = $vk->api('photos.getAlbums',
						array(
								'uid'=>$_SESSION['user_data']['id_user_s'],
								'access_token'=>$_SESSION['slacl']['user']['vk_auth_data']->access_token)
							);
		$id_album = self::getIdAlbum($_SESSION['user_data']['id_user_s']);
		$isset_album = 0;
		foreach($response['response'] as $album){
			if($id_album == $album['aid']){
				$isset_album = 1;
				break;
			}
		}
		if(0 == $isset_album){
			$id_album = self::createAlbumVk($title_album, $description_album);
		}

		if(!empty($id_album)){
			$response_get_server = $vk->api('photos.getUploadServer',array('aid'=>$id_album,'access_token'=>$_SESSION['slacl']['user']['vk_auth_data']->access_token));
			if(isset($response_get_server['response']['upload_url'])){
				$fpath = realpath(dirname(__FILE__).'/../../web/'.$img_path);
				$post_params['file1'] = '@'.$fpath;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $response_get_server['response']['upload_url']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
				$result = curl_exec($ch);
				curl_close($ch);

				$result = json_decode($result);
				if(isset($result->server)){
					$response_save_photo = $vk->api('photos.save',
						array(
							'aid'=>$result->aid,
							'caption'=>$description_image,
							'server'=>$result->server,
							'photos_list'=>$result->photos_list,
							'hash'=>$result->hash,
							'access_token'=>$_SESSION['slacl']['user']['vk_auth_data']->access_token
						));

				}
			}
		}
		return true;
	}

	/**
	 * AddImageToAlbumFb
	 * @static
	 * @param $title string
	 * @param $description string
	 * @param $img_path string
	 * @return bool success or fail
	 */
	static private  function addImageToAlbumFb($title_album, $description_album, $description_image, $img_path){
		try{
			$graph_url = "https://graph.facebook.com/me/albums?access_token=".$_SESSION['slacl']['user']['facebook_session']->access_token;
			$user_albums = json_decode(file_get_contents($graph_url));
			$id_album = self::getIdAlbum($_SESSION['user_data']['id_user_s']);
			$isset_album = 0;
			foreach($user_albums->data as $album){
				if($album->id == $id_album){
					$isset_album = 1;
					break;
				}
			}
			if(0 == $isset_album){
				$id_album = self::createAlbumFb($title_album, $description_album);
			}

			$fpath = realpath(dirname(__FILE__).'/../../web/'.$img_path);

			$file= 'http://my.website.com/my-application/sketch.jpg';
			$data = array(basename($file) => "@".$fpath,
				"caption" => $description_image,
				"access_token" => $_SESSION['slacl']['user']['facebook_session']->access_token
			);

			$ch = curl_init();
			$url = "https://graph.facebook.com/".$id_album."/photos";
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			curl_exec($ch);
			curl_close($ch);

		}catch(Exception $e){
			echo $e;
			die('.');
		}
		return true;
	}

	/**
	 * CreateAlbumFb
	 * @static
	 * @param $title string name of the new albul fb
	 * @param $description string description of the new albul fb
	 * @return int id new album
	 */
	static private function createAlbumFb($title, $description){
		$social = slSocial::getConfig();
		$facebook = new Facebook(array(
			'appId'  => $social['APP_API_KEY_FB'],
			'secret' => $social['APP_API_KEY_FB'],
			'cookie' => true
		));
		$params = array( 'method'  => 'photos.createAlbum',
					'name' 	=> $title,
					'description' 	=> $description,
					'uid'			=> $_SESSION['user_data']['id_user_s'],
					'visible'		=> 'everyone',
					'access_token'	=> $_SESSION['slacl']['user']['facebook_session']->access_token
				);

		$result = $facebook->api(
					$params
				);

		self::updateIdAlbum($result['object_id'], $_SESSION['user_data']['id_user_s']);
		return $result['object_id'];
	}

	/**
	 * CreateAlbumFb
	 * @static
	 * @param $title string name of the new albul vk
	 * @param $description string description of the new albul vk
	 * @return int id of the new album
	 */
	static private function createAlbumVk($title, $description){
		$social = slSocial::getConfig();
		$result = false;
		$vk = new vkapi($social['VK_APP_ID'], $social['VK_APP_PKEY']);
		$response_create_album = $vk->api('photos.createAlbum',array('title'=>$title,'privacy'=>0, 'comment_privacy'=>0, 'description'=>$description,'access_token'=>$_SESSION['slacl']['user']['vk_auth_data']->access_token));
		if(isset($response_create_album['response']['aid'])){
			self::updateIdAlbum($response_create_album['response']['aid'], $_SESSION['user_data']['id_user_s']);
			$result = $response_create_album['response']['aid'];
		}
		return $result;
	}

	/**
	 *GetIdAlbum
	 *@static
	 *@param $id_user int social id author of the album
	 *@return int id album or null if user don't have album yet
	 */
	static private function getIdAlbum($id_user_s){
		$res = Q::create('users')
						->select('id_album')
						->where('id_social = '.$id_user_s)
						->useValue('id_album')
						->one()
						->exec();
		return $res;
	}

	/**
	 * UpdateAlbumId
	 * @static
	 * @param $id_album int id of the album which should be updated
	 * @param $id_user int social id author of the album
	 * @return bool
	 */
	static private function updateIdAlbum($id_album, $id_user_s){
		Q::create('users')
			->update(array('id_album' => $id_album))
			->where('id_social = '.$id_user_s)
			->exec();
		return true;
	}

    /**Сheck whether the user has entered the group
     * @static
     * @return bool|string
     */
    static public function isInGroup() {
        if(!slSocialACL::isLoggedIn()) {return 'Exeption: not isset user data';}
        $result = false;
        if ('fb' == slSocialACL::getLastAuthorizedKey()) {
            $result = SocialFacebook::isInGroup();
        } elseif ('vk' == slSocialACL::getLastAuthorizedKey()){
            $result = SocialVk::isInGroup();
        }

        return $result;
    }

}