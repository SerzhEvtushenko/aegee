<div class="like_box">
    <div class="fb_container">
        <div id="fb-root"></div>
        {literal}
            <script>(function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId="+FB_APP_ID;
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>
        {/literal}
        <div class="fb-like" data-href="{$og_url}" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
    </div>
    <div class="vk_container">
        <script type="text/javascript" src="//vk.com/js/api/openapi.js?105"></script>
        {literal}
            <script type="text/javascript">
                VK.init({apiId: VK_APP_ID, onlyWidgets: true});
            </script>
        {/literal}

        <!-- Put this div tag to the place, where the Like block will be -->
        <div id="vk_like"></div>
        {literal}
            <script type="text/javascript">
                VK.Widgets.Like("vk_like", {type: "button"});
            </script>
        {/literal}
    </div>
</div>
<div class="clear"></div>