<?php

Class IndexController extends slController {

    public function actionDefault() {
	    $news['aegee'] = News::getList(1, false, 'aegee', 3);
	    $news['partners'] = News::getList(1, false, 'partners', 3);

        $this->view->news       = $news;
        $this->view->events     = Event::getEventsToMainPage( 6);
        $this->view->partners   = Partner::loadList(C::create()->where(array('category'=>2)));
        $this->view->sliders    = HomepageSlider::getList();
    }

    public function actionAbout(){
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('about');
        $this->view->route_name = 'about';

    }

    public function actionAegeeEurope(){
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('about_aegee_europe');
        $this->view->route_name = 'aegee_europe';
    }

    public function actionAegeeKyiv(){
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('about_aegee_kiev');
        $this->view->route_name = 'aegee_kyiv';
    }

    public function actionHowToJoin(){
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('how_to_join');
    }

    public function actionDictionary() {
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('dictionary');
    }

    public function actionOurSU() {
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('our_su');
    }

    public  function actionSUAbroad() {
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('su_abroad');
    }

    public  function actionOrganizers() {
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('organizers');
    }

    public  function actionAboutUkraine() {
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('about_ukraine');
    }

    public  function actionTravelTips() {
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('travel_tips');
    }

    public function actionSuFaq()
    {
        $this->view->setTemplate('index/static.tpl');
        $this->view->page = StaticPage::loadOneBySlug('su_faq');
    }

    public function actionSaveFeedback(){
        $result['status'] = false;
        if ($data = $this->route->getVar('data')) {
            $result = Feedback::saveData($data);
        }

        $this->echoJSON($result);
    }

    public function actionPartners(){
        StaticPage::setMetaData('partners');

        $this->view->partners       = Partner::getList();
        $this->view->route_name     = 'partners';
    }

    public function actionVisa(){
	    $this->view->setTemplate('index/static.tpl');
        $slug = $this->route->getVar('slug', false);

        StaticPage::setMetaData('visa');

        $visa_list      = Visa::getList();

        $this->view->page  = Visa::getCurrentItem($slug, $visa_list);
//        $this->view->page               = $visa_list;
        $this->view->route_name         = 'visa';

    }

    public function actionFaq(){
        StaticPage::setMetaData('faq');

        $this->view->faq            = Faq::loadList(C::create()->where(array('is_active'=>1))->orderBy('_position ASC'));
        $this->view->route_name     = 'faq';
    }

    public function actionContacts(){
        StaticPage::setMetaData('contacts');

        $this->view->contacts       = StaticPage::loadOneBySlug('contacts');
        $this->view->route_name     = 'contacts';
        $this->view->board          = Board::getList();
    }

    public function actionTest(){
	    die('.');
    }

}