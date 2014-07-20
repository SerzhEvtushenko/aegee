<?php

/**
 * slModel Partner Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 12.08.2013 00:20:13
 */
class Partner extends BasePartner {

    const NORMAL = 1;
    const INFORM = 2;

    static public function getList(){
        $partners = Partner::loadList(C::create()->orderBy('_position ASC'));

        $result = array();
        foreach($partners as $partner){
            $result[$partner['category']][] = $partner;
        }

        return $result;
    }

    static public function getListOnMain(){
        return Partner::loadList(C::create()->where(array('category'=>self::NORMAL))->orderBy('_position ASC'));
    }
}
