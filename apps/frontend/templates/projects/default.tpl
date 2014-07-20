<section class="news_index internal_page">
    <div class="min_width clearfix">
        <h1>{mlt general/menu_projects}</h1>
        <ul class="news_list  clearfix projects">
            {foreach from=$projects item=item}
                <li class="news news_item {if !isset($item.main_image.sizes.big.link)} without_image{/if}">
                    {if isset($item.main_image.sizes.big.link)}
                        <a class="img" href="{url_to project_detail from=$item}" title="{html $item.title}">
                            <img src="{$item.main_image.sizes.big.link}" alt="{html $item.title}">
                        </a>
                    {/if}
                    <div class="info_container">
                        <a  class="title" href="{url_to project_detail from=$item}" title="{html $item.title}">{$item.title}</a>
                        {if $item.id_coordinator > 0}
                            <div class="coordinator">{mlt general/coordinator}: {$item.user.title}</div>
                        {/if}
                        <div class="txt">{$item.short_description}</div>
                    </div>
                </li>
            {/foreach}
        </ul>

        {include file='_paging.tpl'}

    </div>
</section>

