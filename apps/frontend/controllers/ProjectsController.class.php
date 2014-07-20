<?php

Class ProjectsController extends slController {

    public function actionDefault() {
	    StaticPage::setMetaData('projects');

	    $this->view->projects       = Project::getList($this->route->getVar('page_number', 1));
	    $this->view->pager          = slPaginator::getInfo();
	    $this->view->link__         = 'projects/';
	    $this->view->pre_page_link  = 'page/';
	    $this->view->route_name     = 'projects';

    }

	public function actionDetail(){
		if ($slug = $this->route->getVar('slug')) {
			$project = Project::loadOneBySlug($slug);

			if (!$project || (0 == $project->is_active)) {
				throw new slRouteNotFoundException('');
			}

			StaticPage::setMetaData('projects');
			MetainfoAbility::mergeMetaInfoWithArray($project->toArray());
			$this->view->project = $project;
		}else{
			throw new slRouteNotFoundException('');
		}
	}

}