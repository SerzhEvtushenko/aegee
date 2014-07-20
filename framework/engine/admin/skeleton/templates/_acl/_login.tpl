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
</html>