<div class="index_slider min_width">
    <div class="items_holder">
        {foreach from=$sliders item=item iteration=i}
            {if isset($item.image.sizes.main.link)}
                <div class="item {if 1 != $i}none{/if}">
                    <img src="{$item.image.sizes.main.link}" alt=""/>
                    <p>{$item.description}</p>
                </div>
            {/if}
        {/foreach}
        <div class="toggles">
            {foreach from=$sliders item=item iteration=i}
                <i class="dot {if 1 == $i}act{/if}"></i>
                <i class="dot"></i>
            {/foreach}
        </div>
    </div>
</div>

<section class="news_index">
    <div class="min_width clearfix">
        <h1>{mlt 'mainpage/events_title'}</h1>
        <ul class="news_list">
            {foreach from=$events item=event}
                <li class="li">
                    {if isset($event.main_image.sizes.small.link)}
                        <img src="{$event.main_image.sizes.small.link}" alt="{$event.title}"/>
                        <div class="text">
                    {else}
                        <div class="text without_image">
                    {/if}
                        <div class="dtcell">
                            <a href="{url_to event_detail from=$event}" title="{$event.title}">
                                <h2>{$event.title}</h2>
                                {if strlen($event.start_date)}
                                    <div class="date">{date $event.start_date 'd M'}</div>
                                {/if}
                                <p>{$event.short_description}</p>
                            </a>
                        </div>
                    </div>
                </li>
            {/foreach}
            {*<li class="li ">*}
                {*<div class="text without_image">*}
                    {*<div class="dtcell">*}
                        {*<h2><a href="{url_to events}" title="All events">{mlt 'mainpage/all_events_link'}</a></h2>*}
                    {*</div>*}
                {*</div>*}
            {*</li>*}
        </ul>
    </div>
</section>

<section class="partners_index">
    <div class="min_width clearfix">
        <h1>{mlt 'mainpage/news_title'}</h1>
        <ul class="news_list">
            {foreach from=$news.aegee item=item}
                <li class="li">
                    {if isset($item.main_image.sizes.big.link)}
                        <img src="{$item.main_image.sizes.big.link}" alt="{$item.title}"/>
                        <div class="text">
                    {else}
                        <div class="text without_image">
                    {/if}
                        <div class="dtcell">
                            <a href="{url_to news_detail from=$item}" title="{$item.title}">
                                <h2>{$item.title}</h2>
                                <p>{$item.short_description}</p>
                            </a>
                        </div>
                    </div>
                </li>
            {/foreach}
            {foreach from=$news.partners item=item}
                <li class="li">
                    {if isset($item.main_image.sizes.big.link)}
                    <img src="{$item.main_image.sizes.big.link}" alt="{$item.title}"/>
                    <div class="text">
                        {else}
                        <div class="text without_image">
                            {/if}
                            <div class="dtcell">
                                <a href="{url_to news_detail from=$item}" title="{$item.title}">
                                    <h2>{$item.title}</h2>
                                    <p>{$item.short_description}</p>
                                </a>
                            </div>
                        </div>
                </li>
            {/foreach}
        </ul>
    </div>
</section>

{if StaticPage::mltIsset('mainpage/member_of_the_month_avatar')}
    <section class="news_index">
        <div class="min_width clearfix">
            <h1>{mlt 'mainpage/member_of_the_month_title'}</h1>
            <div class="member_holder clearfix">
                <div class="member_image">
                    <img src="{mlt 'mainpage/member_of_the_month_avatar'}" alt="{mlt 'mainpage/member_of_the_month_name'}"/>
                </div>
                <div class="member_content">
                    <div class="member_title">
                        {mlt 'mainpage/member_of_the_month_name'}
                    </div>
                    <div class="member_description">
                        {mlt 'mainpage/member_of_the_month_description'}
                    </div>
                </div>
            </div>
        </div>
    </section>
{else}
    <section class="news_index">
    </section>
{/if}

<section class="partners_index">
    <div class="min_width clearfix">
        <h1>{mlt 'mainpage/partners'}</h1>
        <ul class="partners_list clearfix">
            {foreach from=$partners item=partner}
                <li><div class="dtable">
                        <div class="dtcell">
                            <a href="{$partner.website}" target="_blank">
                                <img src="{$partner.image.link}" alt="{$partner.website}"/>
                            </a>
                        </div>
                    </div></li>
            {/foreach}
        </ul>
    </div>
</section>