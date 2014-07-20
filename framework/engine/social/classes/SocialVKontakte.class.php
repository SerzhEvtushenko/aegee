<?php
/**
 * Work with vk api
 * @author Evtushenko Sergey
 * @version 1.0
 * User: Grey
 * Date: 11.07.12
 * Time: 13:33
 * To change this template use File | Settings | File Templates.
 */

class SocialVKontakte{

//for social.yml
//SETTINGS_VK: 'friends'
//REDIRECT_URI: 'social/vk_callback'
//FIELDS_VK: 'uid, first_name, last_name,photo,sex,bdate



//full fields list id, first_name, last_name, nickname, screen_name, sex, bdate (birthdate), city, country, timezone, photo, photo_medium, photo_big, has_mobile, rate, contacts, education, online, counters.
//name_case падеж для склонения имени и фамилии пользователя. Возможные значения: именительный – nom, родительный – gen, дательный – dat, винительный – acc, творительный – ins, предложный – abl. По умолчанию nom.
	/**
	 * Get base user data from vk
	 * @static
	 * @param $vk object vk object
	 * @param bool $social vk params
	 * @return array user data
	 */
	static public function getUserData($vk){
        $social     = slSocial::getConfig();
		$response   = $vk->api('getProfiles',
                                array(
                                    'uids'=> slSocialACL::getCurrentUser('vk_auth_data', 'vk')->user_id,
                                    'fields'=>$social['FIELDS_VK']
                                ));
		$user       = $response['response'][0];
		return $user;
	}
	/**
	 * Get user's friends
	 * @static
	 * @param $vk object vk object
	 * @param bool $social vk params
	 * @return array
	 */
	static public function getFriends($id_user){
        $social = slSocial::getConfig();
        $vk = new vkapi($social['VK_APP_ID'], $social['VK_APP_SECRET_KEY']);
		$response1 = $vk->api('friends.get', array('uid'=>$id_user,'fields'=>$social['FIELDS_VK']));
        $friends = $response1['response'];
		foreach($friends as $key=>$friend){
			$friends[$key]['name'] = $friend['first_name'].' '.$friend['last_name'];
		}

		return $friends;

	}
    /**Сheck whether the user has entered the group in VK
     * @static
     * @return string
     */
    static public function isInGroup(){
        if(slSocialACL::isLoggedIn('vk')) {
            $social = slSocial::getConfig();
            $vk = new vkapi($social['VK_APP_ID'], $social['VK_APP_SECRET_KEY']);
            $response = $vk->api('groups.isMember', array('gid'=>$social['VK_GROUP'],'uid'=>slACL::getCurrentUser('id_social')));
        }
        return ( (isset($response['response'])) && (1 == $response['response']) ) ? true : false;
    }



}
