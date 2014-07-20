<?php

Class ftlHelperAdminBadge extends ftlBlock {

    protected $_is_inline = false;

    public function process($params) {
        $info   = $params[0];
        $res    = '';

        if (isset($info['badge'])) {
            if (!is_array($info['badge'])) {
                $info['call'] = array(
                    $info['model'],
                    $info['badge']
                );
            } else {
                $info['call'] = array_values($info['badge']);
            }
            $badge_message = call_user_func($info['call']);
            if ($badge_message) {
                $res = '&nbsp;<span class="badge badge-important pull-right">'.$badge_message.'</span>';
            }
        }

        return $res;
    }

}