<?php


Class ftlHelperAdminNotifications extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        $messages = slView::getInstance()->getMessages();
        $res = '';

        if (!empty($messages)) {
            $res = '<div class="alert alert-success clearfix">'.
                    '<a href="javascript:;" data-dismiss="alert" class="close">×</a>'.
                    '<h4 class="alert-heading fleft">&#10003;</h4><div class="fleft" style="margin-left: 20px">';

            foreach($messages as $item) {
                $res .= $item .'<br/>';
            }
            $res .= '</div></div>';
        }
        return $res;
    }
}