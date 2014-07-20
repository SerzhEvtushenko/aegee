<div class="well">
    <a type="cancel" class="btn btn-grey pull-left" href="admin/dashboard/">
        <i class="icon-arrow-left"></i>
    </a>

    <h3>&nbsp;Настройки</h3>
    <div class="clearfix"></div>
</div>

<ul class="nav nav-tabs" id="plugin-tabs">
    <li class="active"><a id="tab-general" data-toggle="tab" href="{$_ftl.route.full_url}#general">Общие</a></li>
    <li><a id="tab-personal" data-toggle="tab" href="{$_ftl.route.full_url}#personal">Пользователя</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="general">

    {form settings class="form-horizontal"}
    <input type="hidden" name="settings[_init]" value="0" />
    {var data=$settings_data}
        <fieldset>
            {foreach from=$fields_settings key=field_name item=field_info}
            {if $field_name == "id"}
                {continue}
            {/if}

            <div class="control-group{if isset($errors[$field_name])} error{/if}">
                <label class="control-label" for="data-{$field_name}">{$field_info.title}</label>
            {include file="list/_elements/".$field_info['type'].".tpl"}
            </div>
            {/foreach}

            <div class="form-actions">
                <a type="cancel" class="btn btn-grey" href="admin/{$module}/">
                    <i class="icon-arrow-left"></i>
                    К списку
                </a>
                <button type="submit" class="btn btn">
                    <i class="icon-ok"></i>
                    Применить
                </button>

                <button type="submit" class="btn btn-primary" name="close" value="1">
                    <i class="icon-ok icon-white"></i>
                    Сохранить и закрыть
                </button>

            </div>

        </fieldset>
    {/form} <!-- .form -->
    </div>
    {*<div class="tab-pane" id="personal">*}
    {*{form user class="form-horizontal"}*}
    {*<input type="hidden" name="user[_init]" value="0" />*}
    {*{var data=$user_data}*}
        {*<fieldset>*}
            {*{foreach from=$fields_user key=field_name item=field_info}*}
                {*{if $field_name == "id"}*}
                    {*{continue}*}
                {*{/if}*}

                {*<div class="control-group{if isset($errors[$field_name])} error{/if}">*}
                    {*<label class="control-label" for="data-{$field_name}">{$field_info.title}</label>*}
                {*{include file="list/_elements/".$field_info['type'].".tpl"}*}
                {*</div>*}
            {*{/foreach}*}

            {*<div class="form-actions">*}
                {*<a type="cancel" class="btn btn-grey" href="admin/{$module}/">*}
                    {*<i class="icon-arrow-left"></i>*}
                    {*К списку*}
                {*</a>*}
                {*<button type="submit" class="btn btn">*}
                    {*<i class="icon-ok"></i>*}
                    {*Применить*}
                {*</button>*}

                {*<button type="submit" class="btn btn-primary" name="close" value="1">*}
                    {*<i class="icon-ok icon-white"></i>*}
                    {*Сохранить и закрыть*}
                {*</button>*}

            {*</div>*}

        {*</fieldset>*}
    {*{/form} <!-- .form -->*}
    {*</div>*}

</div>

{literal}


<script type="text/javascript">
    $(".datepicker_on").datepicker({
        'dateFormat': 'yy-mm-dd'
    });

    // Chosen multiselect
    $(".chzn-select").chosen();
    $('.redactor_content').redactor({
        lang: 'ru',
        imageUpload: '/admin/files/upload/',
        imageGetJson: '/admin/files/list/',
        buttons: ['formatting', '|', 'bold', 'italic', '|', 'blockidea', '|', 'html'],
        buttonsCustom: {
            blockidea: {
                title: 'Idea',
                callback: function(obj, event, key) {
//                    console.log(obj, event, key);
                    obj.insertHtml('<p>hello Idea!</p>');
                }
            }
        }
    });
    $('#plugin-tabs a').click(function(evt) {
        $.cookie('active_tab', evt.target.id);
    });
    if (active_tab = $.cookie('active_tab')) {
        $('#'+active_tab).tab('show');
    }

</script>
{/literal}
