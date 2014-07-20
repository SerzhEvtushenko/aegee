<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <base href="{$_baseUrl}" >
    {meta_info}

    <meta property="og:url" content="{$og_url}" />

    <meta property="fb:admins" content="100001810733706" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />

    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" href="css/flags.css" type="text/css" media="screen, projection"/>
    <link rel="stylesheet" href="css/main.css?{$revision}" type="text/css" media="all, screen, projection"/>

    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />

    <script type="text/javascript" src="js/libs/modernizr.js"></script>
    <script type="text/javascript" src="js/libs/jquery-1.7.1.js"></script>
    <script type="text/javascript" src="js/libs/jquery.uniform.js"></script>
    <script type="text/javascript" src="js/libs/jquery.maskedinput.js"></script>
    <script type="text/javascript" src="js/script.js?{$revision}"></script>

    <script>
        var FB_APP_ID = '{$social.FB_APP_ID}';
        var FB_SCOPE = '{$social.SETTINGS_FB}';
        var VK_APP_ID = '{$social.VK_APP_ID}';
        var baseUrl = '{$_baseUrl}';
    </script>

    {use_css}
    {use_js}
    {literal}
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-22584718-3']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>
    {/literal}
</head>

<body>

<div class="wrapper">
    <div id="fb-root"></div>

    {include file="_header.tpl"}
    <div class="middle">
        {$template_content}
    </div>
    {include file="_footer.tpl"}
</div>
{include file="_popup.tpl"}
</body>
</html>
