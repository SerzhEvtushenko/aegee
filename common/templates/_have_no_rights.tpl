<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>BMS Login</title>
    <base href="{$_ftl.route.baseUrl}" >
    <link rel="stylesheet" type="text/css" href="css/initial.css" />
    <link rel="stylesheet" type="text/css" href="css/general.css" />
    <link rel="stylesheet" type="text/css" href="css/main.css" />
    <link rel="icon" href="images/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />

    {use_js}
    {use_css}
</head>
<body>
<div class="login_box">
    <form method="post" action="">
        <div class="ml10 c_r">
            {$message}<br/>
            Try to login with another credentinals:
        </div>
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