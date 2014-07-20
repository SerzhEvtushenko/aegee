<section class="blog_post user_page">
    <div class="min_width clearfix page">
        <div class="blog_white_box">
            <div class="blog_text">
                {if isset($bordMember.avatar_in.link)}
                    <img class="board_member_avatar js_change_photo" src="{$bordMember.avatar_in.sizes.big.link}" data-basesrc="{$bordMember.avatar_in.sizes.big.link}" data-src="{$bordMember.avatar_out.sizes.big.link}">
                    <img class="none" src="{$bordMember.avatar_out.sizes.big.link}">
                {elseif isset($bordMember.avatar_out.sizes.big.link)}
                    <img class="board_member_avatar" src="{$bordMember.avatar_out.sizes.big.link}">
                {/if}

                <h3>{$bordMember.user.first_name} {$bordMember.user.last_name}</h3>

                <div class="content">
                    <div class="desc">
                        <p>{$bordMember.category_title}</p>
                        <p>{$bordMember.email}</p>
                        <p>{$bordMember.phone}</p>
                    </div>
                    {$bordMember.description}
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</section>