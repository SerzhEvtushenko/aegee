<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="utf-8" />
    <title>{$admin_title}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <base href="{$_baseUrl}" />
    <link rel="stylesheet" href="admin/css/bootstrap.min.css" />
    <link rel="stylesheet" href="admin/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="admin/css/solve.login.css" />

</head>
<body class="clearfix">
<div id="logo">
    {$admin_title}
</div>
<div id="loginbox">
    <form id="loginform" class="form-vertical" action="" method="post">
        <p>Enter username and password to continue.</p>
        <div class="control-group">
            <div class="controls">
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-user"></i></span><input name="slacl_login[email]" type="text" placeholder="Username"{if isset($login)} value="{$login}"{/if} />
                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-lock"></i></span><input name="slacl_login[password]" type="password" placeholder="Password" />
                </div>
            </div>
        </div>
        {if isset($slacl_error)}
        <div class="alert alert-error">
            <button data-dismiss="alert" class="close">×</button>
            <strong>Error!</strong> {$slacl_error}
        </div>
        {/if}
        <div class="form-actions">
            {*<span class="pull-left"><a href="login_recover.html" class="flip-link">Lost password?</a></span>*}
            <span class="pull-right"><input type="submit" class="btn btn-inverse" value="Login" /></span>
        </div>
    </form>
</div>

<script src="admin/js/jquery.min.js"></script>
<script src="admin/js/bootstrap.min.js"></script>
<script src="admin/js/solve.login.js"></script>
</body>
</html>{*

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
    <link href="admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="admin/css/jquery-ui-1.8.16.custom.css" rel="stylesheet">
    <link href="admin/css/ui.daterangepicker.css" rel="stylesheet">
*}
{*<link href="admin/css/chosen.intenso.css" rel="stylesheet">*}{*

    <link href="admin/css/admin.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="js/html5.js"></script>
    <![endif]-->

    <script src="admin/js/jquery.min.js"></script>
    <script src="admin/js/jquery-ui.min.js"></script>
    <script src="admin/js/bootstrap.min.js"></script>
    <script src="admin/js/daterangepicker.jQuery.js"></script>
    <script src="admin/js/jquery.form.js"></script>
    <script src="admin/js/jquery.cookie.js"></script>
    <script src="admin/js/date.js"></script>

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
            <a class="brand" href="#">Admin Panel - Solve</a>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span3">
        </div><!--/span-->
        <div class="span9">
            <form class="well form-horizontal span5" method="POST" action="">
                <div class="control-group">
                    <label for="login-input" class="control-label">Login</label>
                    <div class="controls">
                        <input class="input-medium" name="slacl_login[login]" id="login-input"/>
                    </div>
                </div>
                <div class="control-group">
                    <label for="login-password" class="control-label">Password</label>
                    <div class="controls">
                        <input type="password" class="input-medium" name="slacl_login[password]" id="login-password"/>
                    </div>
                </div>
                <div class="controls">
                    <input type="submit" class="btn" value="LOGIN" />
                </div>
            </form>

        </div><!--/span-->
    </div><!--/row-->

    <hr>

    <footer>
        <p>&copy; Solve 2012</p>
    </footer>

</div><!--/.fluid-container-->

</body>
</html>*}
