

<section class="news_index">
    <div class="min_width clearfix">

        <h1>{mlt general/menu_faq}</h1>
        <ul class="faq_list faq_accordion">
            {foreach from=$faq item=item}
                <li class="li">
                    <div class="faq_title"><i class="icon icon_faq"></i>{$item.question}</div>
                    <div class="faq_content clearfix"><div class="faq_content_inner">
                            {$item.answer}
                        </div></div>
                </li>
            {/foreach}
        </ul>
    </div>
</section>