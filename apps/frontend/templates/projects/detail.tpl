<link rel="stylesheet" href="css/jquery.fancybox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/libs/jquery.fancybox.pack.js"></script>

<div class="middle project_inner">
    <div class="min_width">

        <h1 class="h1">{$project.title}</h1>

        {include file="index/_like_box.tpl"}

        {if $project.id_coordinator > 0}
        <div class="info">
            <div class="coordinator">{mlt general/coordinator} {$project.user.title}</div>
        </div>
        {/if}

        <div class="clear"></div>
        <div class="content">
            {$project.description}

            {if  count($project.gallery) > 0 }
                <div class="gallery thumbnails clearfix">
                    {foreach from=$project.gallery item=image iteration = i}
                        <a class="thumbnail fancybox {if $i>6} none{/if}" rel="gallery1" href="{$image.link}" title="">
                            <img src="{$image.sizes.small.link}" alt="" />
                        </a>
                    {/foreach}
                </div>
            {/if}
        </div>

        <div class="clear"></div>
    </div>
</div>