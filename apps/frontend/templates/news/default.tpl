<section class="news_index internal_page">
    <div class="min_width clearfix">
        <h1>{mlt general/menu_news}</h1>
        {if $issetPartnersNews}
            <div class="sub-menu">
                {if 'aegee' == $currentCategory}
                    <span class="link act">{mlt general/sub_menu_aegee}</span>
                    <a class="link" href="{$link__}?category=partners">{mlt general/sub_menu_partners}</a>
                {else}
                    <a class="link" href="{$link__}">{mlt general/sub_menu_aegee}</a>
                    <span class="link act">{mlt general/sub_menu_partners}</span>
                {/if}
            </div>
        {/if}
        <ul class="news_list  clearfix">
            {foreach from=$news item=item}
                <li class="news news_item {if !isset($item.main_image.sizes.big.link)}without_image{/if}">
                    {if isset($item.main_image.sizes.big.link)}
                        <a class="img" href="{url_to news_detail from=$item}" title="{html $item.title}">
                            <img src="{$item.main_image.sizes.big.link}" alt="{html $item.title}">
                        </a>
                    {/if}
                    <a  class="title" href="{url_to news_detail from=$item}" title="{html $item.title}">{$item.title}</a>
                    {if !empty($item._created_at)}
                        <div class="date">{date $item._created_at 'd F Y '}</div>
                    {/if}
                    <div class="txt">{$item.short_description}</div>
                    <div class="tags">{news_tags $item.tags}</div>
                </li>
            {/foreach}
        </ul>
        {include file='_paging.tpl'}

    </div>
</section>


