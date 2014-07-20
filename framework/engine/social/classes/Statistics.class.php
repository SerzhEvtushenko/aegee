<?php
/**
 * Created by JetBrains PhpStorm.
 * User: serg
 * Date: 04.08.12
 * Time: 21:40
 * To change this template use File | Settings | File Templates.
 */
class Statistics{

    /**
     * add item to friend's statistics
     * @static
     * @param $social_key string vk or fb
     * @param string $access_token string
     */
    static public function addItemStatistics($social_key, $access_token = ''){
        if(isset($_SESSION['user_data']['id_user'])){
            $statistics = new StatisticItem();
            $statistics->id_user        = $_SESSION['user_data']['id_user'];
            $statistics->id_user_s      = $_SESSION['user_data']['id_user_s'];
            $statistics->social_key     = $social_key;
            $statistics->access_token   = $access_token;
            $statistics->save();
        }


    }

    /**
     * Process item statistics
     * Get user friends and add their to db
     * @static
     *
     */
    static public function processStatistics(){
        $statistics = StatisticItem::loadOne(C::create()->where('0 = is_processed'));
        if($statistics){
            if('vk' == $statistics->social_key){
                $friends = SocialVk::getFriends($statistics->id_user_s);
            }else{
                $friends = SocialFacebook::getFriends($statistics->access_token);
            }
            Statistics::saveFriends($statistics->id, $friends, $statistics->social_key, $statistics->access_token);
            $statistics->is_processed = 1;
            $statistics->processed_time = date('Y-m-d');
            $statistics->save();
        }


    }

    /**
     * Save user friends for statistics
     * @static
     * @param $id_user int user id
     * @param $friends array user's friends
     * @param $social_key string vk or fb
     * @param $access_token string
     */
    static public function saveFriends($id_user, $friends, $social_key, $access_token){
        $_SESSION['friends'] = $friends;
        $array = array();
        foreach($friends as $item){
            if('fb' == $social_key){
                $friend = Q::create('statistic_lists')
                                    ->where('id_friend = '.$item->id)
                                    ->exec();
                if(empty($friend)){
                    $friend = SocialFacebook::getUserData($item->id, $access_token);
                }
            }else{
                $friend = $item;
                $friend['gender']       =   (1 == $friend['sex']) ? 'female' : 'male';
                $friend['birthday']     =   isset($friend['bdate']) ? $friend['bdate'] : '';
                $friend['id']           =   $friend['uid'];
            }

            $array[] = array(
                'id_user'       => $id_user,
                'id_friend'     =>  isset($friend['id'])            ? $friend['id']             : '',
                'name'          =>  isset($friend['name'])          ? $friend['name']           : '',
                'birthday'      =>  isset($friend['birthday'])      ? date("Y-m-d", strtotime($friend['birthday']))       : '',
                'gender'        =>  isset($friend['gender'])        ? $friend['gender']         : ''
            );
        }

        if(!empty($array)){
            Q::create('statistic_lists')
                ->insert($array)
                ->exec();
        }
    }

}
