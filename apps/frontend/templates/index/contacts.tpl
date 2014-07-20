<section class="news_index contacts_container">
    <div class="min_width clearfix">
        <h1>{$contacts.title}</h1>

        <div class="content">

            <div class="fleft contact_description">
                <div class="sub_title">
                    {$contacts.description}
                </div>
                <div class="info news_inner">
                    <div>{$contacts.phone_title}: {$contacts.phone}</div>
                    <div>{$contacts.email_title}: {$contacts.email}</div>
                    <div class="icon_holder"><i class="icon icon_fb"></i><a href="{$contacts.fb_link}" target="_blank">{$contacts.fb_title}</a></div>
                    <div class="icon_holder"><i class="icon icon_vk"></i><a href="{$contacts.vk_link}" target="_blank">{$contacts.vk_title}</a></div>
                    <div class="icon_holder"><i class="icon icon_tw"></i><a href="{$contacts.youtube_link}" target="_blank">{$contacts.youtube_title}</a></div>
                </div>
            </div>
            <div class="fright contact_info">
                <img src="{$contacts.board.sizes.big.link}">
            </div>
            <div class="clear"></div>
        </div>
    </div>
</section>

<section class="board_container">
    <div class="min_width clearfix">
        <h1>{$contacts.board_title}</h1>
        <ul class="board_holder  clearfix">
            {foreach from=$board.current_board item=item}
                <li>
                    <img class="board_avatar" src="{$item.user.small_avatar}">
                    <a href="{url_to user from=$item}" class="name">{$item.name}</a>
                    <div class="category category_title">{$item.category_title}</div>
                    <div class="contacts">
                        <div class="email">{$item.email}</div>
                        <div class="phone">{$item.phone}</div>
                    </div>
                </li>
            {/foreach}
        </ul>
    </div>
</section>

<section class="news_index">
    <div class="min_width clearfix">
        <h1>{$contacts.previous_board_title}</h1>
        <ul class="board_holder old_board clearfix">
            {foreach from=$board.old_boards key=key item=users}
                <li>
                    <div class="years">{$key}</div>
                    <ul class="board_sub_holder">
                        {foreach from=$users item=user}
                            {if strlen($user.name)}
                                <li><b>{$user.name}</b>: {$user.category_title}</li>
                            {/if}
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
        </ul>
    </div>
</section>
