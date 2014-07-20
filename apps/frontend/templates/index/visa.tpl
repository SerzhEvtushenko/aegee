
<section class="news_index visa">
    <div class="min_width clearfix">
        <h1>{mlt general/menu_visa}</h1>
        <div class="visas_holder clearfix">
            <ul class="menu">
                {foreach from=$visa_list item=item}
                    <li  {if $current_visa_item.slug == $item.slug} class="active" {/if}><i class="icon icon_arr"></i>
                        <a  href="{url_to visa_detail from=$item}">{$item.title}</a>
                    </li>
                    <li class="hr">
                        <hr/>
                    </li>
                {/foreach}
            </ul>
            <div class="content">
                {$current_visa_item.description}
            </div>
        </div>
    </div>
</section>