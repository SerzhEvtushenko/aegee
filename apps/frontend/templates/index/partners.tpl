
<section class="news_index">
    <div class="min_width clearfix">
        <h1>Sponsors</h1>
        <ul class="sponsors_list clearfix">
            {foreach from=$partners[1] item=partner iteration=i}
                <li class="clearfix">
                    <a class="img_container" href="{$partner.website}" target="_blank">
                        <img src="{$partner.image.link}" alt="{$partner.website}"/>
                    </a>
                    <div class="info">
                        <a target="_blank" class="title" href="{$partner.website}">{$partner.title}</a>
                        {$partner.description}
                    </div>
                </li>
            {/foreach}
        </ul>
    </div>
</section>


<section class="partners_index">
    <div class="min_width clearfix">
        <h1>{mlt 'mainpage/partners'} </h1>
        <ul class="partners_list clearfix">
            {foreach from=$partners[2] item=partner}
                <li><div class="dtable"><div class="dtcell"><a href="{$partner.website}" target="_blank"><img src="{$partner.image.link}" alt="{$partner.website}"/></a></div></div></li>
            {/foreach}
        </ul>
    </div>
</section>

