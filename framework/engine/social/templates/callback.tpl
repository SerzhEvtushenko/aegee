<script type="text/javascript">
        var user_data = '{$isset_user_data}';
        var liked_group = '{$liked_group}';
        var social_key = '{$social_key}';
        window.opener.logged_for_like(user_data, liked_group, social_key);
        window.close();
</script>