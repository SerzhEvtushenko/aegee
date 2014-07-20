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
    {include file="_header.tpl"}

    <div class="middle">
        <div class="min_width page_not_found">
            <div class="title">404</div>
            <div class="description">{mlt general/page_not_found_title}</div>
            <a href="{url_to default_route}" class="link">{mlt general/page_not_found_to_main}</a>
        </div>
    </div>
    {include file="_footer.tpl"}
</div>
{include file="_popup.tpl"}
</body>
</html>
