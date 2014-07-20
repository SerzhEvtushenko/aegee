<?php

Class NewsController extends slController {

    public function actionDefault() {
        StaticPage::setMetaData('news');

	    $category   = $this->route->getVar('category', 'aegee');
	    $tags       = $this->route->getVar('tag', false);

	    $partnersNews = Q::create('news n')
		    ->select('n.id')
		    ->leftJoin('news_mlt m', 'm.id = n.id')
		    ->where('m.is_active = 1')
		    ->andWhere('m.lang = \'' . MLT::getActiveLanguage().'\'')
		    ->andWhere('id_category = ' . News::CATEGORY_PARTNERS)
	        ->one()
	        ->exec();



	    $this->view->issetPartnersNews = isset($partnersNews);
        $this->view->news           = News::getList($this->route->getVar('page_number', 1), $tags, $category);
        $this->view->pager          = slPaginator::getInfo();
        $this->view->link__         = 'news/' . (strlen($tags) > 0 ? 'tag/'.$tags.'/' : '');
        $this->view->pre_page_link  = 'page/';
        $this->view->route_name     = 'news';
	    $this->view->currentCategory = $category;
	    $this->view->post_page_link = ('partners' == $category) ? '?category=partners' : '';;
    }

    public function actionDetail(){
        if ($slug = $this->route->getVar('slug')) {

            $news = News::loadOneBySlug($slug);

            if (!$news || (0 == $news->is_active)) {
                throw new slRouteNotFoundException('');
            }

            StaticPage::setMetaData('news');
            MetainfoAbility::mergeMetaInfoWithArray($news->toArray());

	        $this->view->news = $news;
	        $this->view->another_news     = $news->getAnotherNews();

        }else{
            throw new slRouteNotFoundException('');
        }
    }



}