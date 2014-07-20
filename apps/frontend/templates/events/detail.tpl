<link rel="stylesheet" href="css/jquery.fancybox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/libs/jquery.fancybox.pack.js"></script>


<div class="middle news_inner">
    <div class="min_width">

        <h1 class="h1">{$event.title}</h1>

        {include file="index/_like_box.tpl"}
        <div class="clear"></div>
        <div class="content">
            <div class="category">
                {if $event.id_category == Event::CATEGORY_EUROPE}{mlt events/status_europe}{else}{mlt events/status_local}{/if}
            </div>
            {if $event.id_coordinator > 0}
                <div class="coordinator">{mlt general/coordinator} {$event.user.title}</div>
            {/if}
            <div class="info">
                {if strlen($event.start_date)}
                    <div class="date">{mlt events/start_at} {date $event.start_date 'd F Y'}
                        {if strlen($event.end_date)}<div class="end_date">{mlt events/finish_at} {date $event.end_date 'd F Y'}</div>{/if}
                    </div>
                {/if}

                <div class="date">
                    {if strlen($event.dedline)}
                        <div class="dedline">{mlt events/dedline_at} {date $event.dedline 'd F Y'}</div>
                    {/if}
                    {if $event.fee > 0}
                        <div class="fee">{mlt events/fee} {$event.fee} {currency $event.currency}</div>
                    {/if}
                </div>
            </div>

            <div class="tags clearfix">{event_tags $event.tags}</div>


            {$event.description}
            {if  count($event.gallery) > 0 }
                <div class="gallery thumbnails clearfix">
                    {foreach from=$event.gallery item=image iteration = i}
                        <a class="thumbnail fancybox {if $i>6} none{/if}" rel="gallery1" href="{$image.link}" title="">
                            <img src="{$image.sizes.small.link}" alt="" />
                        </a>
                    {/foreach}
                </div>
            {/if}
            {*{include file="index/_comments.tpl"}*}
        </div>


        {if isset($another_events)}
            <ul class="news_list clearfix">
                {foreach from=$another_events}
                    <li class="li">
                        {if isset($item.main_image.sizes.small.link)}
                            <img src="{$item.main_image.sizes.small.link}" alt="{$item.title}"/>
                            <div class="text">
                        {else}
                            <div class="text without_image">
                        {/if}
                            <div class="dtcell">
                                <a href="{url_to event_detail from=$item}" title="{$item.title}">
                                    <h2>{$item.title}</h2>
                                    {if strlen($item.start_date)}
                                        <div class="date">{date $item.start_date 'd M'}</div>
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