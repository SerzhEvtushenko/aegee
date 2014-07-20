<script type="text/javascript">
    var id_object = '{$data.id}';
</script>

<div class="row-fluid">
    <div class="span12">

        <div class="widget-box">
            <div class="widget-content nopadding">




{admin_notifications}

{if isset($errors) && !empty($errors)}
    <div class="alert alert-error">
        <a href="javascript:;" class="close">×</a>
        <h4 class="alert-heading">Ошибки при сохранении</h4>
        {foreach from=$errors key=error_code item=field}
            {foreach from=$field item=error}
            {$error.message}<br/>
            {/foreach}
        {/foreach}
    </div>
{/if}


<ul class="nav nav-tabs" id="plugin-tabs">
    <li class="active"><a id="tab-basic" data-toggle="tab" href="{$_ftl.route.full_url}#basic">Информация</a></li>
    {if isset($module_structure.tabs)}
        {foreach from=$module_structure.tabs key=tab_key item=tab_info}
        <li><a id="tab-{$tab_key}" data-toggle="tab" href="{$_ftl.route.full_url}#{$tab_key}">{$tab_info.title}</a></li>
        {/foreach}
    {/if}
</ul>


<div class="tab-content">
    <div class="tab-pane active" id="basic">

    {form data class="form-horizontal"}
        {*<fieldset>*}
            {foreach from=$fields key=field_name item=field_info}

            {if 'hr_title' == $field_info.type}
                {*<hr/>*}
                <h4 class="m20">{$field_info.title}</h4>
                {continue}
            {elseif 'hr' == $field_info.type}
                <hr/>
                {continue}
            {/if}
            {if $field_name == "id"}
                <input type="hidden" name="data[_id]" value="id"/>
                {continue}
            {/if}
            <div class="control-group{if isset($errors[$field_name])} error{/if}">
                {if $field_info.title != "no_title"}
                <label class="control-label" for="data-{$field_name}">{$field_info.title}
                    {if isset($field_info.lang)}<img class="lang" alt="{$field_info.lang}" src="admin/images/flags/flag_{$field_info.lang}.gif"/>{/if}
                </label>
                {/if}
                {if isset($field_info.template)}
                {include file=$field_info['template']}
                {elseif !empty($module_structure['actions']['edit']) && in_array($field_name, $module_structure['actions']['edit']['view'])}
                <span class="view_item {$field_info.type}">{$data[$field_name]}&nbsp;</span>
                {else}
                {include file="list/_elements/".$field_info['type'].".tpl"}
                {/if}
            </div>
            {/foreach}

            <div class="form-actions">
                <a type="cancel" class="btn btn-grey" href="admin/{$module}/">
                    <i class="icon-arrow-left"></i>
                    К списку
                </a>
                <button type="submit" class="btn submit-button">
                    <i class="icon-ok"></i>
                    Применить
                </button>

                {if isset($use_back_to_shop_btn)}
                    <button type="submit" class="btn btn-primary submit-button" name="back_to_shop" value="1">
                        <i class="icon-ok icon-white"></i>
                        Сохранить и вернуться к магазину
                    </button>
                {else}
                    <button type="submit" class="btn btn-primary submit-button" name="close" value="1">
                        <i class="icon-ok icon-white"></i>
                        Сохранить и закрыть
                    </button>
                {/if}

            </div>

        {*</fieldset>*}
        <input type="hidden" name="editing_language" value="{$editing_language}">
    {/form} <!-- .form -->
    </div>
{if isset($module_structure.tabs)}
    {foreach from=$module_structure.tabs key=tab_key item=tab_info}
        <div class="tab-pane" id="{$tab_key}">
{include file="list/_tabs/".($tab_key).".tpl"}
        </div>

    {/foreach}
{/if}

</div>

{literal}
<script type="text/javascript">
    $('.delete_image_handler').click(function(){
        var t = $(this);
        if (!confirm('Are you sure you want to delete this image?')) return false;
        $.ajax({
            'url': t.attr('href'),
            'dataType': 'json',
            'type': 'post'
        }).done(function(res){
            if (res.res) {
                t.parents('li').remove();
            } else {
                alert('Ошибка при удалении изображения');
            }
            t.css('opacity',0.5);
        }).fail(function(){
            alert('Ошибка при выполнении запроса, попробуйте перезагрузить страницу')
        }).always(function(){
            t.css('opacity',1);
        });
        return false;
    });


    $(".datepicker_on").datetimepicker({
        'dateFormat': 'yy-mm-dd',
        'timeFormat': 'HH:mm'
    });


    $('.redactor_content').redactor({
        cleanup: true,
        minHeight: 200,
        lang: 'ru',
        imageUpload: '/admin/files/upload/',
        imageGetJson: '/admin/files/list/',
        custom_css: ['css/style.css?2'],
        base_href: base_url,
        buttons: ['html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', '|',
            'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
            'image', 'video', 'file', 'table', 'link', '|',
            'fontcolor', 'backcolor',
            '|', 'horizontalrule', '|', 'end', 'fullscreen'],
        buttonsCustom: {
            end: {
                title: 'To the end',
                callback: function(obj, event, key) {
                    placeCaretAtEnd($('[contenteditable=true]')[0]);
                }
            }
        },
        plugins: ['fullscreen:startFullscreen']
    });

    $('#plugin-tabs a').click(function(evt) {
        $.cookie('active_tab', evt.target.id);
    });

    if (active_tab = $.cookie('active_tab')) {
        $('#'+active_tab).tab('show');
    }

    $('.submit-button').click(function(e) {
        e.preventDefault();
        if ($('.redactor_content').length) {
            $('.redactor_content').each(function(index, item) {
                $(item).val($(item).getCode());
            });
        }
        if ($(this).attr('name') == 'close') {
            $('#data-form').append('<input type="hidden" name="close" value="1" />');
        }
        if ($(this).attr('name') == 'back_to_shop') {
            $('#data-form').append('<input type="hidden" name="back_to_shop" value="1" />');
        }
        $('#data-form').submit();
        return true;
    });

</script>
{/literal}
            </div>
        </div>
    </div>
</div>