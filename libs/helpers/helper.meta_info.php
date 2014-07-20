<?php

Class ftlHelperMetaInfo extends ftlBlock {

    protected $_is_inline = true;

    public function process($params) {

        $res = '
			<?php $__meta_info = MetainfoAbility::getMergedMetaInfo(); ?>

		    <title><?php echo $__meta_info[\'meta_title\']; ?></title>
			<meta name="title" content="<?php echo $__meta_info[\'meta_title\']; ?>" />
			<meta name="keywords" content="<?php echo $__meta_info[\'meta_keywords\']; ?>" />
			<meta name="description" property="og:description" content="<?php echo $__meta_info[\'meta_description\']; ?>" />

			<meta property="og:title" content="<?php echo $__meta_info[\'meta_title\']; ?>" />
			<meta property="og:description" content="<?php echo $__meta_info[\'meta_description\']; ?>"/>

			<meta name="author" content="europe" />
			<meta property="og:type" content="company" />
			<meta property="og:site_name" content="Europe" />
        ';
        return $res;
    }

}