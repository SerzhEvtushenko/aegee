<link rel="stylesheet" href="css/jquery.fancybox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/libs/jquery.fancybox.pack.js"></script>

<div class="middle news_inner">
    <div class="min_width">


        <h1 class="h1">{$object.title}</h1>
        {include file="index/_like_box.tpl"}
        <div class="clear"></div>
        <div class="content">
            <div class="info">
                <div class="date">{date $object._created_at 'd F Y'}</div>
            </div>
            {$object.description}

            {if  count($object.gallery) > 0 }
                <div class="gallery thumbnails clearfix">
                    {foreach from=$object.gallery item=image iteration = i}
                        <a class="thumbnail fancybox {if $i>6} none{/if}" rel="gallery1" href="{$image.link}" title="">
                            <img src="{$image.sizes.small.link}" alt="" />
                        </a>
                    {/foreach}
                </div>
            {/if}
        </div>

        {if count($another_objects)>0}
            <ul class="news_list news_list_left clearfix">
                {foreach from=$another_objects }
                    <li class="li">
                        {if isset($item.main_image.sizes.big.link)}
                            <img src="{$item.main_image.sizes.big.link}" alt="{$item.title}"/>
                            <div class="text">
                        {else}
                            <div class="text without_image">
                        {/if}
                                <div class="dtcell">
                                    <a href="{url_to aegee_today_detail from=$item}" title="{$item.title}">
                                        <h2>{$item.title}</h2>
                                        {if strlen($item._creared_at)}
                                            <div class="date">{date $item._creared_at 'd M'}</div>
                                        {/if}
                                        <p>{$item.short_description}</p>
                                    </a>
                                </div>
                            </div>

                    </li>
                {/foreach}
            </ul>
        {/if}
        <div class="clear"></div>
    </div>
</div>