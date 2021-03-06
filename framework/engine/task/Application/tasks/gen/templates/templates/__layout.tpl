<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>__PROJECT__ &middot; Default</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/dev_tools/dev_main.css" rel="stylesheet">
    <base href="{$_baseUrl}" >

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="js/html5.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="shortcut icon" href="images/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="images/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="images/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="images/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon-57-precomposed.png">
{use_css}
</head>

<body>
<div id="wrap">
    <div class="container-narrow">

        <div class="masthead">
            <ul class="nav nav-pills pull-right">
                <li class="active"><a href="{url_to index}" title="__PROJECT__ Project, just get it solved">Home</a></li>
                <li><a href="http://wiki.solve-project.org/">Wiki</a></li>
                <li><a href="dev_tools/">Dev Tools</a></li>
            </ul>
            <h3 class="muted">__PROJECT__ Project</h3>
        </div>

        <hr>

    {$template_content}


    </div> <!-- /container -->
    <div id="push"></div>
</div>
<div id="footer">
    <div class="container">
        <p class="muted credit">__PROJECT__ project <a href="http://solve-project.org/">Solve Framework</a> based.</p>
    </div>
</div>
<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/jquery-1.8.2.min.js"></script>
<script src="js/bootstrap.min.js"></script>
{use_js}
</body>
</html>
