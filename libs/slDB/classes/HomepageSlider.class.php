<?php

/**
 * slModel HomepageSlider Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 16.10.2013 01:02:35
 */
class HomepageSlider extends BaseHomepageSlider {

    static public function getList(){
        return self::loadList(C::create()->where(array('is_active'=>1))->orderBy('_position ASC'));
    }
}
