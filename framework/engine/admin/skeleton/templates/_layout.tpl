<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Admin Panel - bootstrap based</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <base href="{$_baseUrl}" />

    <!-- Le styles -->
    <link href="css/admin/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin/jquery-ui-1.8.16.custom.css" rel="stylesheet">
    <link href="css/admin/ui.daterangepicker.css" rel="stylesheet">
    {*<link href="css/admin/chosen.intenso.css" rel="stylesheet">*}
    <link href="css/admin/admin.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="js/html5.js"></script>
    <![endif]-->

    <script src="js/admin/jquery.min.js"></script>
    <script src="js/admin/jquery-ui.min.js"></script>
    <script src="js/admin/bootstrap.min.js"></script>
    <script src="js/admin/daterangepicker.jQuery.js"></script>
    <script src="js/admin/jquery.form.js"></script>
    <script src="js/admin/jquery.cookie.js"></script>
    <script src="js/admin/date.js"></script>

    <script src="js/redactor/redactor/redactor.js"></script>
    <script src="js/redactor/redactor/fullscreen.js"></script>

    <script type="text/javascript">
        var base_url    = '{$_baseUrl}';
        var module_url  = '{$module}';
    </script>
    {use_css}
    {use_js}
</head>

<body>
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="admin/">Admin Panel - Solve</a>
            <div class="btn-group pull-right">
                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="icon-user"></i>&nbsp;{$current_user.name}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="admin/settings/">Настройки</a></li>
                    <li class="divider"></li>
                    <li><a href="admin/logout/">Выход</a></li>
                </ul>
            </div>
            <div class="nav-collapse">
                <ul class="nav">
                    {*<li class="active"><a href="admin/">Modules</a></li>*}
                    {*<li><a href="admin/">Announcement</a></li>*}
                    {*<li><a href="admin/">News</a></li>*}
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span3">
{include file="_left_menu.tpl"}
        </div><!--/span-->
        <div class="span9">
            <ul class="breadcrumb none">
                <li>
                    <a href="admin/">Dashboard</a> <span class="divider">/</span>
                </li>
                <li>
                    <a href="admin/posts/">Posts</a> <span class="divider">/</span>
                </li>
                <li class="active">Editing</li>
            </ul>
{$template_content}

        </div><!--/span-->
    </div><!--/row-->

    <hr>

    <footer>
        <p>&copy; Solve 2012</p>
    </footer>

</div><!--/.fluid-container-->

</body>
</html>