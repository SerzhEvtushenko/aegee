<section class="news_index internal_page">
    <div class="min_width clearfix">
        <h1>{mlt general/menu_members_of_the_month}</h1>

        <ul class="news_list  clearfix">
            {foreach from=$members item=item}
                <li class="news news_item {if !isset($item.main_image.sizes.big.link)}without_image{/if}">
                    {if isset($item.main_image.sizes.big.link)}
                        <a class="img" href="{url_to news_detail from=$item}" title="{html $item.title}">
                            <img src="{$item.main_image.sizes.big.link}" alt="{html $item.title}">
                        </a>
                    {/if}
                    <a  class="title" href="{url_to member_detail from=$item}" title="{html $item.title}">{$item.title}</a>
                    {if !empty($item._created_at)}
                        <div class="date">{date $item._created_at 'd F Y '}</div>
                    {/if}
                    <div class="txt">{$item.short_description}</div>

                </li>
            {/foreach}
        </ul>
        {include file='_paging.tpl'}

    </div>
</section>


