<header class="header">
    <div class="top_line">
        <div class="min_width clearfix">
            <ul class="lang">
                {if 'en'== $active_language}
                    <li><a class="link" href="ua{$uri}" title="{mlt general/header_lang_ua}"><i class="flag flag-ua"></i>{mlt general/header_lang_ua}</a></li>
                    <li><span class="link"><i class="flag flag-us"></i>{mlt general/header_lang_en}</span></li>
                {else}
                    <li><span class="link" ><i class="flag flag-ua"></i>{mlt general/header_lang_ua}</span></li>
                    <li><a href="en{$uri}" title="" class="link"><i class="flag flag-us"></i>{mlt general/header_lang_en}<i class="shadow"></i><i class="shadow right"></i></a></li>
                {/if}
                <input type="hidden" id="current-language" value="{$active_language}">
            </ul>

            <ul class="controls">
                <li><a class="link dashded show_feedback_js" href="" title="{mlt general/header_feedback}"><span>{mlt general/header_feedback}</span></a></li>
                {if slACL::isLoggedIn()}
                    <li><a class="link" href="{url_to profile}" title="{mlt general/header_profile}"><span>{mlt general/header_profile}</span></a></li>
                    <li><a class="link" href="{url_to logout}" title="{mlt general/header_exit}"><span>{mlt general/header_exit}</span></a>
                {else}
                    <li><a class="link" href="{url_to registration}" title="{mlt general/header_registration}"><span>{mlt general/header_registration}</span></a></li>
                    <li><a class="link dashded login_popup_btn" href="" title="{mlt general/header_login}"><span>{mlt general/header_login}</span></a>
                        <div class="login_popup">
                            <a class="link dashded login_popup_btn" href="" title="{mlt general/header_login}"><span>{mlt general/header_login}</span><i class="shadow"></i><i class="shadow right"></i></a>

                            <div class="form form_login">
                                <fieldset class="control">
                                    <label class="label">{mlt general/header_form_login}</label>
                                    <div class="input_holder">
                                        <input id="acl_email" value="" />
                                        {*<div class="placeholder">login</div>*}
                                    </div>
                                </fieldset>
                                <fieldset class="control">
                                    <label class="label">{mlt general/header_form_pass}</label>
                                    <div class="input_holder">
                                        <input id="acl_password" type="password" value="" />
                                        <div class="password_recovery_show_popup">{mlt general/header_recover_password}</div>
                                    </div>
                                    <div class="text error none">{mlt general/header_form_error} </div>
                                </fieldset>
                                <fieldset class="control form_footer">
                                    <span class="btn btn_input" id="acl_send_login_request">{mlt general/header_form_btn}</span>
                                </fieldset>
                            </div>
                        </div>
                    </li>
                {/if}
            </ul>
        </div>
    </div>
    <div class="min_width clearfix">
        {if 'default_route' == $route_name}
            <span class="logo"><img src="images/logo.png" alt="{mlt general/header_logo_alt_text}"/></span>
        {else}
            <a class="logo" href="{url_to default_route}" title="{mlt general/header_logo_alt_text}"><img src="images/logo.png" alt="{mlt general/header_logo_alt_text}"/></a>
        {/if}
        <div class="slogan">{mlt general/header_slogan}</div>
        <div class="social_links">
            <a class="icon icon_fb" target="_blank" href="{mlt general/header_facebook_link}" title="{mlt general/header_facebook_link}"></a>
            <a class="icon icon_vk" target="_blank" href="{mlt general/header_vk_link}" title="{mlt general/header_vk_link}"></a>
            <a class="icon icon_yt" target="_blank" href="{mlt general/header_youtube_link}" title="{mlt general/header_youtube_link}"></a>
        </div>
    </div>
</header>

<nav class="navigation">
    <div class="min_width">
        <ul class="menu">
            <li class="li">
                {if 'default_route' == $route_name}
                    <span class="link act">{mlt general/menu_main}</span>
                {else}
                    <a class="link" href="{url_to default_route}" title="{mlt general/menu_main}">{mlt general/menu_main}</a>
                {/if}
            </li>
            <li class="li">
                <span class="link {if ('about' == $route_name) || ('aegee_europe' == $route_name) || ('aegee_kyiv' == $route_name)}act{/if}" >{mlt general/menu_about}<i class="icon icon_arr down"></i></span>
                <div class="sub_menu">
                    <span class="link" >{mlt general/menu_about}<i class="icon icon_arr down"></i><i class="shadow"></i><i class="shadow right"></i></span>
                    <div class="sub_holder">
                        {if 'about' == $route_name}
                            <span class="sub_link act">{mlt general/menu_what_is_aegee}</span>
                        {else}
                            <a class="sub_link "href="{url_to about}" title="{mlt general/menu_what_is_aegee}">{mlt general/menu_what_is_aegee}</a>
                        {/if}
                        {if 'aegee_europe' == $route_name}
                            <span class="sub_link act"  >{mlt general/menu_aegee_europe}</span>
                        {else}
                            <a class="sub_link"  href="{url_to aegee_europe}" title="{mlt general/menu_aegee_europe}">{mlt general/menu_aegee_europe}</a>
                        {/if}
                        {if 'aegee_kyiv' == $route_name}
                            <span class="sub_link act " >{mlt general/menu_aegee_kyiv}</span>
                        {else}
                            <a class="sub_link " href="{url_to aegee_kyiv}" title="{mlt general/menu_aegee_kyiv}">{mlt general/menu_aegee_kyiv}</a>
                        {/if}
                    </div>
                </div>
            </li>
            <li class="li">
                {if 'how_to_join' == $route_name}
                    <span class="link" >{mlt general/menu_how_to_join}</span>
                {else}
                    <a class="link" href="{url_to how_to_join}" title="{mlt general/menu_how_to_join}">{mlt general/menu_how_to_join}</a>
                {/if}
            </li>
            <li class="li">
                <span class="link {if ('projects' == $route_name) || ('news' == $route_name) || ('working_groups' == $route_name)}act{/if}" >{mlt general/menu_activity}<i class="icon icon_arr down"></i></span>
                <div class="sub_menu">
                    <span class="link" >{mlt general/menu_activity}<i class="icon icon_arr down"></i><i class="shadow"></i><i class="shadow right"></i></span>
                    <div class="sub_holder">
                        {if 'events' == $route_name}
                            <span class="sub_link act" >{mlt general/menu_events}</span>
                        {else}
                            <a class="sub_link" href="{url_to events}" title="{mlt general/menu_events}">{mlt general/menu_events}</a>
                        {/if}
                        {if 'projects' == $route_name}
                            <span class="sub_link act" >{mlt general/menu_projects}</span>
                        {else}
                            <a class="sub_link"  href="{url_to projects}" title="{mlt general/menu_projects}">{mlt general/menu_projects}</a>
                        {/if}
                        {if 'news' == $route_name}
                            <span class="sub_link act" >{mlt general/menu_news}</span>
                        {else}
                            <a class="sub_link " href="{url_to news}" title="{mlt general/menu_news}">{mlt general/menu_news}</a>
                        {/if}
                        {*<a class="sub_link {if 'working_groups' == $route_name}act{/if}" href="{url_to working_groups}" title="{mlt general/menu_working_group}">{mlt general/menu_working_group}<i class="icon icon_arr right"></i></a>*}

                    </div>
                </div>
            </li>
            <li class="li">
                {if 'partners' == $route_name}
                    <span class="link act" >{mlt general/menu_partners}</span>
                {else}
                    <a class="link" href="{url_to partners}" title="{mlt general/menu_partners}">{mlt general/menu_partners}</a>
                {/if}
            </li>
            <li class="li">
                <span class="link {if ('faq' == $route_name) || ('visa' == $route_name) }act{/if}">{mlt general/menu_useful_information}<i class="icon icon_arr down"></i></span>
                <div class="sub_menu">
                    <span class="link" >{mlt general/menu_useful_information}<i class="icon icon_arr down"></i><i class="shadow"></i><i class="shadow right"></i></span>
                    <div class="sub_holder">
                        {if 'faq' == $route_name}
                            <span class="sub_link act" >{mlt general/menu_faq}</span>
                        {else}
                            <a class="sub_link" href="{url_to faq}" title="{mlt general/menu_faq}">{mlt general/menu_faq}</a>
                        {/if}
                        {if 'visa' == $route_name}
                            <span class="sub_link" >{mlt general/menu_visa}</span>
                        {else}
                            <a class="sub_link" href="{url_to visa}" title="{mlt general/menu_visa}">{mlt general/menu_visa}</a>
                        {/if}

                    </div>
                </div>
            </li>
            <li class="li">
                {if 'contacts' == $route_name}
                    <span class="link act" >{mlt general/menu_contacts}</span>
                {else}
                    <a class="link" href="{url_to contacts}" title="{mlt general/menu_contacts}">{mlt general/menu_contacts}</a>
                {/if}
            </li>
        </ul>
    </div>
</nav>


