<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">

    <title>{$admin_title}</title>
    <base href="{$_baseUrl}" />
    <script>var base_url = '{$_baseUrl}';</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="admin/css/bootstrap.min.css" />
    <link rel="stylesheet" href="admin/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="admin/css/colorpicker.css" />
    <link rel="stylesheet" href="admin/css/datepicker.css" />
    <link rel="stylesheet" href="admin/css/uniform.css" />
    <link rel="stylesheet" href="admin/css/select2.css" />
    <link rel="stylesheet" href="admin/css/solve.main.css?1" />
    <link rel="stylesheet" href="admin/css/solve.grey.css" class="skin-color" />
    <link href="js/redactor/redactor/redactor.css" rel="stylesheet">

    <script src="admin/js/jquery-1.9.0.min.js"></script>
    <script src="admin/js/jquery-ui-1.10.0.custom.js"></script>

    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
    <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>

    <script src="admin/js/timepicker.js"></script>

    <script src="js/redactor/redactor/redactor.js"></script>
    <script src="js/redactor/redactor/fullscreen.js"></script>

    <script src="admin/js/jquery.cookie.js"></script>
    <script src="admin/js/jquery.form.js"></script>
    <script src="admin/js/admin.js"></script>
</head>
<body>
    <div id="header">
        <h1><a href="admin/">{$admin_title}</a></h1>
    </div>

    <div id="user-nav" class="navbar navbar-inverse">
        <ul class="nav btn-group">
            {*{if $show_settings}*}
                <li class="btn btn-inverse"><a title="" href="admin/settings/"><i class="icon icon-cog"></i> <span class="text">Settings</span></a></li>
            {*{/if}*}
            <li class="btn btn-inverse"><a title="" href="admin/logout/"><i class="icon icon-share-alt"></i> <span class="text">Logout</span></a></li>
        </ul>
    </div>

    {include file="_left_menu.tpl"}

    <div id="content">
        <div id="content-header">
            <h1>{$module_title}</h1>

            <div class="btn-group">
                {foreach from=$mlt_languages item=language}
                    <a href="{$_ftl.route.full_url}?set_language={$language}" class="btn{if $active_model_language == $language} active{/if}">{$language|mb_strtoupper}</a>
                {/foreach}
            </div>
        </div>
        <div id="breadcrumb"></div>
        <div class="container-fluid">
            {$template_content}

            <div class="row-fluid">
                <div id="footer" class="span12">
                    2013 &copy; AEGEE (Association des Etats Généraux des Etudiants de l’Europe / European Students’ Forum).
                </div>
            </div>
        </div>
    </div>

    <script src="admin/js/bootstrap.min.js"></script>
    <script src="admin/js/bootstrap-colorpicker.js"></script>
    <script src="admin/js/jquery.uniform.js"></script>
    <script src="admin/js/select2.min.js"></script>
    <script src="admin/js/solve.js"></script>
    {*<script src="admin/js/solve.form_common.js"></script>*}
    {use_js}
</body>
</html>