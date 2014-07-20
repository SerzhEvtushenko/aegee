{use_css "form.css"}
<div class="wrapper">
    <h2>Database Profile Configurator:</h2>
    <div id="form-container" class="form_container">
        {form database}
            <h3>profile [{$profile_name}]:</h3><br/>
            <label class="form_label" for="input-dbname">Name:</label><input id="input-dbname" type="text" name="data[dbname]" value="{$profile.dbname}" title="" class="input_title" />
            {errors title}
            <div class="clear"></div>

            <label class="form_label" for="input-host">Host:</label><input id="input-host" type="text" name="data[host]" value="{$profile.host}" title="" class="input_title" />
            {errors host}
            <div class="clear"></div>

            <label class="form_label" for="input-user">User:</label><input id="input-user" type="text" name="data[user]" value="{$profile.user}" title="" class="input_title" />
            {errors user}
            <div class="clear"></div>

            <label class="form_label" for="input-password">Password:</label><input id="input-password" type="password" name="data[pass]" value="{$profile.pass}" title="" class="input_title" />
            {errors password}
            <div class="clear separator_clear"></div>

            <label class="form_label">
                <a href="{url_to db}" class="label_red">&larr; back</a>
            </label>
            <span class="button_red " onclick="document.getElementById('database-form').submit();">Save</span>

            <div class="clear separator"></div>
        {/form}
    </div
</div>