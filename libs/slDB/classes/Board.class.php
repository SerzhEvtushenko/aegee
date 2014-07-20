<?php

/**
 * slModel Board Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 14.08.2013 01:28:01
 */
class Board extends BaseBoard {

    public function getUser(){
	    $user = array();
	    if ($this->id_user > 0) {
		    $user = AclUser::loadOne(array('id'=>$this->id_user));

	    }

        return $user;
    }

    static public function
    getList(){

        $category_list = array(
            1   => 'President',
            2   => 'Public Relations',
            3   => 'Fund Raising',
            4   => 'Secretary',
            5   => 'Treasurer',
            6   => 'Human Resources',
            7   => 'IT',
            8   => 'Projects',
            9   => 'Events',
            10  => 'Revcom',
        );

        $honorary_members   = array();
        $current_boar_id    = array();
        $current_board      = array();

        $boards = Q::create('boards b')
                        ->select('b.*, au.title as name')
                        ->leftJoin('acl_users au', 'au.id = b.id_user')
                        ->foldBy('years')
                        ->orderBy('years DESC, _position ASC')
                        ->exec();

        foreach($boards as $key=>$year){
            foreach($year as $k=>$user){
                $boards[$key][$k]['category_title'] = isset($category_list[$user['id_category']])
                    ? $category_list[$user['id_category']]
                    : '';
                if (1 == $user['is_honorary_member']){
                    $honorary_members[] = $user;
                }
            }
        }

        foreach($boards as $key=>$year){
            if (empty($current_board)) {
                $current_board = $year;
                unset($boards[$key]);
                continue;
            }

        }

        foreach($current_board as $user) {
            $current_boar_id[] = $user['id_user'];
        }

        $users = AclUser::loadList($current_boar_id);

        foreach($current_board as $key=>$item){
            foreach($users as $user){
                if ($item['id_user'] == $user['id']) {
                    $current_board[$key]['user'] = $user;
                    break;
                }
            }
        }

        return array(
                'honorary_members'  => $honorary_members,
                'old_boards'        => $boards,
                'current_board'     => $current_board
        );
    }


	public function getCategoryTitle(){
		$category_list = array(
			1   => 'President',
			2   => 'Public Relations',
			3   => 'Fund Raising',
			4   => 'Secretary',
			5   => 'Treasurer',
			6   => 'Human Resources',
			7   => 'IT',
			8   => 'Projects',
			9   => 'Events',
			10  => 'Revcom',
		);


		return array_key_exists($this->id_category, $category_list) ? $category_list[$this->id_category] : '';
	}


}
