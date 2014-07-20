<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Default layout for Blog</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />

    <base href="{$_baseUrl}" >
    <link rel="stylesheet" type="text/css" href="css/initial.css" />
    <link rel="stylesheet" type="text/css" href="css/general.css" />
    <link rel="stylesheet" type="text/css" href="css/dev_tools.css" />
    {use_css}
    {use_js}
</head>
<body class="default_body">
<div class="login_box">
    <form method="post" action="">
        <div class="lb">
            <label>логин:</label>
            <input type="text" class="login_input" name="ffacl_login[login]"/>
        </div>
        <div class="lb">
            <label>пароль:</label>
            <input type="password" class="login_input" name="ffacl_login[password]" />
        </div>
        <div class="lb">
            <label>&nbsp;</label>
            <input type="submit" class="login_button" value="LOGIN" />
        </div>
    </form>
</div>
</body>
</html>