<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>&darr; Context</title>
    <base href="{$_ftl.route.baseUrl}" >
    <link rel="stylesheet" type="text/css" href="css/initial.css" />
    <link rel="stylesheet" type="text/css" href="css/general.css" />
    <script type="text/javascript" src="js/libs/mootools/mootools-core-1.3-yc.js"></script>
    <script type="text/javascript" src="js/libs/mootools/mootools-more-yc.js"></script>
</head>
<body>
  <table class="wrap_table">
      <tr>
          <td>
              <div class="login_box">
                  <span class="access_title">
                      {$message}<br/>
                      <span class="access_tip">
                          login with another user credentials or go <a href="javascript:History.prev();">back</a> to previous page
                      </span>
                  </span>
                  <form class="inline" method="post" id="ffacl-login-form">
                      <input type="text" class="input_login over_text" value="{$login}" name="ffacl_login[login]" tabindex="1" title="login"/>
                      <input type="submit" class="input_submit" value="-->" tabindex="3" />
                      <input type="password" class="input_password over_text" value="" name="ffacl_login[password]" tabindex="2" title="password"/>
                      <div class="clear"></div>
                      <div class="c_r ml10 font12 txt_shadow">{$ffacl_error}&nbsp;</div>
                  </form>
              </div>
          </td>
      </tr>
  </table>
<script type="text/javascript">
{literal}
$$('.over_text').each(function(item) {new OverText(item); });
{/literal}
</script>
</body>
</html>
