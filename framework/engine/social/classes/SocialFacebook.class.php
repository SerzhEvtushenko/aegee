<?php
/**
 * Work with facebook api
 * @author Evtushenko Sergey
 * @version 1.0
 * User: Grey
 * Date: 11.07.12
 * Time: 11:07
 */

class SocialFacebook{
//for social.yml
//    SETTINGS_FB:   'email,read_friendlists, friends_birthday'
//    SETTINGS_FB: 'read_friendlists, user_photos,publish_stream,email, user_likes,user_interests,user_checkins,user_birthday,user_activities,user_education_history,user_events,user_groups,user_location,user_relationship_details,user_about_me,user_work_history'
//    FIELSD_FB: 'che#ckins,events,friends,games,groups,television,likes,movies,music,television,videos'

    /**
	 * @var array methods available in graph api
	 * http://developers.facebook.com/docs/reference/api/
	 */
	static private $_available_methods = array(
		'feed',
		'likes',
		'movies',
		'music',
		'books',
		'notes',
		'permissions',
		'photos',
		'albums',
		'videos',
		'events',
		'groups',
		'checkins',
		'locations',
        'television'
	);
	/**
	 * Get base user data from facebook
	 * @static
	 * @param $id_user string
	 * @param $access_token string
     * @param $avatar 'large', 'normal', 'small','square'
	 * @return array user data
	 */
	static public function getUserData($id_user, $access_token, $avatar = false){
		$graph_url = "https://graph.facebook.com/".$id_user."?access_token=".$access_token;
		$user = (array)json_decode(file_get_contents($graph_url));
        if($avatar){
            $user['avatar_link'] = self::getPhoto($user['id'], $avatar);
        }
		return $user;
	}

	/**
	 * Get user's data from graph api
	 * @static
	 * @param $access_token string  access key
	 * @param $method string the method name
	 * @return mixed
	 */
	static public function getUserDataGraphApi($access_token, $method){
		if(in_array($method, self::$_available_methods)){
			$graph_url = "https://graph.facebook.com/me/".$method."?access_token=".$access_token;
			$result= json_decode(file_get_contents($graph_url));
		}else{
			$result = 'Exeption: not existing method '.$method;
		}

		return $result;
	}

	/**
	 * Get user's friends
	 * @static
	 * @param $access_token
	 * @param bool $nead_avatar flag should be a friends avatar
	 * @return array|mixed
	 */
	static public function getFriends($access_token, $nead_avatar = false){
		$graph_url = "https://graph.facebook.com/me/friends?access_token=".$access_token;
		$friends = json_decode(file_get_contents($graph_url));

		$friends = (array)$friends;
		$friends = $friends['data'];

		if($nead_avatar){
            $friends_new = array();
			foreach($friends as $key=>$friend) {
				$friend = (array)$friend;
				$avatar_link = 'https://graph.facebook.com/'.$friend['id'].'/picture?type=large';
				$friends_new[$key]['uid'] = $friend['id'];
				$friends_new[$key]['first_name'] = $friend['name'];
				$friends_new[$key]['photo'] = $avatar_link;

			}
		}

		return ($nead_avatar) ? $friends_new : $friends;

	}

	/**
	 *Method checks whether the user has voted for this Page
	 * @static
	 * @return string
	 */
	static public function isInGroup(){
//        nead: read_stream
		$social = slSocial::getConfig();
		$result = false;
		if(isset($social['FB_PAGE_ID'])){
			if(slSocialACL::isLoggedIn('fb')){
                $fb = new Facebook(array(
                    'appId' => $social['FB_APP_ID'],
                    'secret' => $social['FB_APP_SECRET_KEY']
                ));
                try {
                    $response = $fb->api($social['FB_PAGE_ID'] . "/members/" . slACL::getCurrentUser('id_social'));
                    if(!empty($response['data'])){
                        $result = !empty($response['data']);
                    }
				} catch(Exception $e) {
                    $result = 'Exeption: fql error. Unset user data and social';
                    slSocialACL::unauthorize('fb');
				}
			}else{
				$result = 'Exeption: not isset user data';
			}
		}else{
			$result = 'Exeption: not isset id like page';
		}
		return $result;
	}

	/**
	 * Return link to user photo some size
	 * @static
	 * @param $id_user int id user
	 * @param null $type varchar(255) type of the photos
	 * @return string link to photo
	 */
	static public function getPhoto($id_user, $type = null){
//		square (50x50)
//		small (50 pixels wide, variable height)
//		normal (100 pixels wide, variable height)
//		large (about 200 pixels wide, variable height)
		$photo_types = array('large', 'normal', 'small','square');
		$photo = 'https://graph.facebook.com/'.$id_user.'/picture';
		if( (!empty($type)) && (in_array($type, $photo_types)) ){
			$photo .= '?type='.$type;
		}
		return $photo;
	}

    /** Send message to the user's wall on fb
     * @static
     * @param $obj_link
     * @param $title
     * @param $description
     * @param null $id_user
     */
    static public function sendMessageToWall($obj_link, $title, $description, $id_user=null){
        try{
            $social = slSocial::getConfig();

            $facebook = new Facebook(array(
                'appId' => $social['FB_APP_ID'],
                'secret' => $social['FB_APP_SECRET_KEY'],
                'cookie' => true
            ));


            $base_url = slRouter::getBaseUrl();

            $src = $base_url.'images/share.png';

            $base_url .= $obj_link;

            $media = array(
                "type"=>"image",
                "src"=> $src,
                "href"=> $base_url
            );
            $media = array($media);

            $attachment = array(
                'name'          =>$title,
                'href'          => $social['url'],
                'description'   =>$description,
                'media'         => $media
            );

            $params = array( 'method'  => 'stream.publish',
                'attachment'  => $attachment,
                'uid'   => $_SESSION['user_data']['user_id'],
                'access_token' =>$_SESSION['facebook']['session']->access_token
            );

            $result = $facebook->api(
                $params
            );

        }catch(Exception $e){
//                todo unset user data and return exception
        }
    }

    /**Send text message to the user
     * @static
     * @param $message string
     * @param $access_token string
     * @return $result
     */
    static public function sendMessageToFb($message = null, $access_token){
        $message = !empty($message) ? $message : 'Hello from my App!';
        $social = slSocial::getConfig();
        $result = false;

        if(slSocialACL::isLoggedIn()){
            $facebook = new Facebook(array(
                'appId'  => $social['FB_APP_ID'],
                'secret' => $social['FB_APP_SECRET_KEY'],
                'cookie' => true
            ));
            $params = array(
                'message'=>$message,
                'link'=>slRouter::getBaseUrl(),
                'caption'=>'bla-bla-bla',
                'access_token'=> $access_token
            );
            try{
                $result = $facebook->api(
                    '/me/feed',
                    "post",
                    $params
                );
            }catch(Exception $e) {
                $result = $e;
            }
        }
        return $result;
    }

    /**
     * Add new checkin to fb. Nead publish_checkins permissions
     * @param $access_token string
     * @param $id_place int
     * @param $lat float
     * @param $lon float
     * @param $message string
     * @param $picture
     * @return bool| or id checkin
     */
    static public function addCheckin($access_token, $id_place, $lat, $lon,$message,$picture){
        $social = slSocial::getConfig();
        $facebook = new Facebook(array(
            'appId'  => $social['FB_APP_ID'],
            'secret' => $social['FB_APP_SECRET_KEY'],
            'cookie' => true
        ));

        try{
            $result =  $facebook->api('/me/checkins', 'POST', array(
                'access_token' => $access_token,
                'place' => $id_place,
                'message' => $message,
                'picture' =>  $picture,
                'coordinates' => json_encode(array(
                        'latitude'  => $lat,
                        'longitude' => $lon,
                        'tags' => 'me')
                )
            ));
        }catch(Exception $e){
            $result = false;
        }
        return $result;

    }


}
