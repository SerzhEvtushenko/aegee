<section class="news_index internal_page events-container">
    <div class="min_width clearfix">
        <h1>Events</h1>
        <div class="sub-menu">
            {if 'all' == $currentFilter}
                <span class="link act">{mlt events/sub_menu_all}</span>
                <a class="link" href="{$link__}?filter=european">{mlt events/sub_menu_european}</a>
                <a class="link" href="{$link__}?filter=local">{mlt events/sub_menu_future}</a>
            {elseif 'european' == $currentFilter}
                <a class="link" href="{$link__}">{mlt events/sub_menu_all}</a>
                <span class="link act">{mlt events/sub_menu_european}</span>
                <a class="link" href="{$link__}?filter=local">{mlt events/sub_menu_future}</a>
            {else}
                <a class="link" href="{$link__}">{mlt events/sub_menu_all}</a>
                <a class="link" href="{$link__}?filter=european">{mlt events/sub_menu_european}</a>
                <span class="link act">{mlt events/sub_menu_future}</span>
            {/if}
        </div>
        <ul class="news_list  clearfix">
            {foreach from=$events item=item}
                <li class="news news_item">
                    {if isset($item.main_image.sizes.small.link)}
                        <a class="img" href="{url_to event_detail from=$item}" title="{html $item.title}">
                            <img src="{$item.main_image.sizes.small.link}" alt="{html $item.title}">
                        </a>
                    {/if}
                    <a  class="title" href="{url_to event_detail from=$item}" title="{html $item.title}">{$item.title}</a>
                    <div class="category">
                        {if $item.id_category == Event::CATEGORY_EUROPE}{mlt events/status_europe}{else}{mlt events/status_local}{/if}
                    </div>

                    <div class="info">
                        {if strlen($item.start_date)}
                            <div class="date">{mlt events/start_at} {date $item.start_date 'd F Y'}
                                {if strlen($item.end_date)}<div class="end_date">{mlt events/finish_at} {date $item.end_date 'd F Y'}</div>{/if}
                            </div>
                        {/if}

                        <div class="date">
                            {if strlen($item.dedline)}
                                <div class="dedline">{mlt events/dedline_at} {date $item.dedline 'd F Y'}</div>
                            {/if}
                            {if $item.fee > 0}
                                <div class="fee">{mlt events/fee} {$item.fee} {currency $item.currency}</div>
                            {/if}
                        </div>
                    </div>

                    <div class="txt">{$item.short_description}</div>
                    <div class="tags clearfix">{event_tags $item.tags}</div>
                </li>
            {/foreach}
        </ul>
        {include file='_paging.tpl'}

    </div>
</section>


