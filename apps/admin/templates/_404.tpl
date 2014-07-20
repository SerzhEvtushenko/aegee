<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <base href="{$_baseUrl}" >
    {meta_info}

    <meta property="fb:admins" content="100001810733706" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />

    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" href="css/flags.css" type="text/css" media="screen, projection"/>
    <link rel="stylesheet" href="css/main.css?{$revision}" type="text/css" media="all, screen, projection"/>

    <!-- что бы телефон не ломался из-за скайпа -->
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />

    <script type="text/javascript" src="js/libs/modernizr.js"></script>
    <script type="text/javascript" src="js/libs/jquery-1.7.1.js"></script>
    <script type="text/javascript" src="js/libs/jquery.uniform.js"></script>
    <script type="text/javascript" src="js/libs/jquery.maskedinput.js"></script>
    <script type="text/javascript" src="js/script.js?{$revision}"></script>

    {use_css}
    {use_js}

    {$settings.google_analytic}
</head>

<body>
{$settings.yandex_metrika}

<div class="wrapper">
    <header class="header">
        <div class="top_line">
            <div class="min_width clearfix">
                &nbsp;
            </div>
        </div>
        <div class="min_width clearfix">
            <span class="logo"><img src="images/logo.png" alt="{mlt general/header_logo_alt_text}"/></span>
            <div class="slogan">{mlt general/header_slogan}</div>
            <div class="social_links">
                <a class="icon icon_fb" target="_blank" href="{mlt general/header_facebook_link}" title="{mlt general/header_facebook_link}"></a>
                <a class="icon icon_vk" target="_blank" href="{mlt general/header_vk_link}" title="{mlt general/header_vk_link}"></a>
                <a class="icon icon_yt" target="_blank" href="{mlt general/header_youtube_link}" title="{mlt general/header_youtube_link}"></a>
            </div>
        </div>
    </header>

    <div class="middle">
        <div class="min_width page_not_found">
            <div class="title">404</div>
            <div class="description">{mlt general/page_not_found_title}</div>
            <a href="{url_to default_route}" class="link">{mlt general/page_not_found_to_main}</a>
        </div>
    </div>
</div>

</body>
</html>
