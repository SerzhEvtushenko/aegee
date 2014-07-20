<?php

Class ftlHelperNewsTags extends ftlBlock {

    public function process($params) {
        $res = '';

        if (isset($params[0])){
            $tags = explode('#', $params[0]);
            foreach($tags as $tag){
                if (!empty($tag)){
                    $res .= '<a href="'.MLT::getActiveLanguage().'/news/tag/'.$tag.'/" title="'.$tag.'">#'.$tag.'</a>';
                }
            }
        }
        return $res;
    }

}