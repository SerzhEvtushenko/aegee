<?php

/**
 * slModel StaticPage Generated with slDBOperator
 *
 * @package aegee
 * @version 1.0
 *
 * created 12.08.2013 00:15:38
 */
class StaticPage extends BaseStaticPage {

    static private $pages = array();

    static public function loadOneBySlug($slug) {
        return self::loadOne(array(
            'slug' => $slug,
            'lang' => MLT::getActiveLanguage()
        ));
    }

    static public function loadSettings() {
        $mlt_ = slMemcached::get('mlt_'.MLT::getActiveLanguage());
        return isset($mlt_['SETTINGS']) ? $mlt_['SETTINGS']: self::loadOneBySlug('settings');
    }

    public function save($with_validation = true, $force_save = false) {
        $result = parent::save($with_validation, $force_save);

        if (1 == $this->translate) {
            self::updateCache();
        }

        return $result;
    }

    public static function updateCache(){
        slMemcached::initialize();

        if (slMemcached::getHandler()) {

            $pages = self::loadList(C::create()->where(array('lang'=>MLT::getActiveLanguage()))->andWhere(array('translate'=>1)));
            $mlt_ = array();

            foreach($pages as $page){
                $mlt_[$page['system_title']] = unserialize($page->_dynamic_values);
            }
            $mainpage = self::loadOne(C::create()->where(array('slug'=>'mainpage','lang'=>MLT::getActiveLanguage())));
            if ($mainpage) {
                $mainpage->loadFiles();
                if (isset($mainpage->member_of_the_month_avatar['link'])) {
                    $mlt_[$mainpage->slug]['member_of_the_month_avatar'] = $mainpage->member_of_the_month_avatar['link'];
                }
            }
            slMemcached::set('mlt_'.MLT::getActiveLanguage(), $mlt_);
        }
    }

    static public function mltIsset($params){
        $result = self::loadCached($params);

        return !empty($result) ? true : false;
    }

    static public function setMetaData($page_name = 'base', $sub_page = ''){
        $page = self::loadCached('METAINFO');
        if (isset($page[$page_name.'_meta_title'.$sub_page])){
            MetainfoAbility::mergeMetaInfoWithArray(array(
                    '_meta_title'       => $page[$page_name.'_meta_title'.$sub_page],
                    '_meta_description' => $page[$page_name.'_meta_description'.$sub_page],
                    '_meta_keywords'    => $page[$page_name.'_meta_keywords'.$sub_page])
            );

        }
    }

    static public function loadCached($params){
        $page = array();
        $item = explode('/', $params);
        if (empty($item)) {
            SL::log('Empty page request for url '.slRouter::getCurrentUri(), 'mlt');
        }

        if (slMemcached::getHandler()) {
            $mlt_ = slMemcached::get('mlt_'.MLT::getActiveLanguage());

            if (!$mlt_) {
                StaticPage::updateCache();
                StaticPage::sendAdminEmail();
            }
            $item[0] = strtolower($item[0]);

            if (!empty($mlt_[$item[0]])){
                $page = $mlt_[$item[0]];
            }
        } elseif (isset(self::$pages[$item[0]])) {
            $page = self::$pages[$item[0]];
        } else {
            $page = self::loadOne(C::create()->where(array('slug'=>$item[0], 'lang'=>MLT::getActiveLanguage())));
            self::$pages[$item[0]] = $page;
        }

        if (isset($item[1])) {
            $result = (isset($page[$item[1]])) ?  $page[$item[1]] : '';

            if (is_array($result)) {
                $result = $result['sizes']['big']['link'];
            }
        } else {

            $result = $page;
        }

        return $result;
    }

    static public function sendAdminEmail(){
        $email = 's.evtyshenko@gmail.com';
        $title  = 'Пустой мемкеш на aegee';
        $body = 'Подгружал мамкеш '.date('d-F-Y H:i:s');

        slMailer::sendMail($email, $title, $body, 'info@aegee.com');
    }

    static public function checkMemcachedMLT(){
        if(slMemcached::getHandler() && !slMemcached ::get('mlt_'.MLT::getActiveLanguage())) {
            self::updateCache();
        }
    }
}
