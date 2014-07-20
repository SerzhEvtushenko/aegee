<?php

/**
 * slModel Revcom Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 28.08.2013 00:58:20
 */
class Revcom extends BaseRevcom {


    public function getUser(){
        return AclUser::loadOne(C::create()->where(array('id'=>$this->id_user)));
    }
}
