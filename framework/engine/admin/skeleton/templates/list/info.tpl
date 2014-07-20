<div class="well">
    <a type="cancel" class="btn btn-grey pull-left" href="admin/{$module}/">
        <i class="icon-arrow-left"></i>
    </a>

    <button type="submit" class="btn btn-info pull-right">
        <i class="icon-eye-open icon-white"></i>
    </button>
    <div class="btn-group pull-right">
        {foreach from=$mlt_languages item=item}
        <a href="{$_ftl.route.full_url}?set_language={$item}" class="btn{if $active_model_language == $item} active{/if}">{$item}</a>
        {/foreach}
    </div>
    <a href="{$_ftl.route.full_url}/fill_language" class="btn btn-inverse pull-right">
        <i class="icon-random icon-white"></i>
        Fill
    </a>

    <h3>&nbsp;{$structure.modules[$module]['title']} | {if !empty($data["title"])}{$data["title"]}{else}создание{/if}</h3>
    <div class="clearfix"></div>
</div>

{admin_notifications}

{if isset($errors)}
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
        <fieldset>
            {foreach from=$fields key=field_name item=field_info}
            {if $field_name == "id"}
            <input type="hidden" name="data[_id]" value="id"/>
            {continue}
            {/if}
            <div class="control-group{if isset($errors[$field_name])} error{/if}">
                {if $field_info.title != "no_title"}
                <label class="control-label" for="data-{$field_name}">{$field_info.title}</label>
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

                <button type="submit" class="btn btn-primary submit-button" name="close" value="1">
                    <i class="icon-ok icon-white"></i>
                    Сохранить и закрыть
                </button>

                <button type="submit" class="btn btn-danger pull-right ml5">
                    <i class="icon-remove icon-white"></i>
                    Удалить
                </button>
            </div>

        </fieldset>
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

    var idea_html = '<p class="case_block text_info clearfix min_width">'
            +'<span class="img"><img class="cb_img" src="/img/case_img1.png" alt="" /></span>'
            +'<span class="cb_content">'
            +'<span class="cbcontent_title">Идея</span>'
            +'<span class="cbcontent_txt">В рамках кампании Walk With Giants, Johnnie Walker рассказывает истории успешных людей, показывая, какой путь они прошли для достижения успеха. В разных странах послами бренда были - Ричард Брэнсон, Льюс Хэмилтон, Николай Фоменко и многие другие, в Украине лицом кампании стал Александр Роднянский.</span>'
            +'</span>'
//            +'</p><p><br/></p>'
            ;
    $(".datepicker_on").datepicker({
        'dateFormat': 'yy-mm-dd'
    });

    var customBlockHtml = function(o) {
        return '<p class="case_block text_info clearfix min_width">'
                +'<span class="img"><img class="cb_img" src="'+ o.img +'" alt="" /></span>'
                +'<span class="cb_content">'
                +'<span class="cbcontent_title">'+ o.title +'</span>'
                +'<span class="cbcontent_txt">'+ o.content +'</span>'
                +'</span>'
    };

     // Chosen multiselect
    $(".chzn-select").chosen();
    $('.redactor_content').redactor({
        convertDivs: true,
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
            'fontcolor', 'backcolor', '|', 'blockidea','blockmechanic','blocktask','blocksolution','blockresults',
            '|', 'horizontalrule', '|', 'end', 'fullscreen'],
        buttonsCustom: {
            blockidea: {
                title: 'Идея',
                callback: function(obj, event, key) {
                    obj.syncCode();
                    obj.insertHtml(customBlockHtml({
                        img: '/img/case_img1.png',
                        title: 'Идея',
                        content: 'В рамках кампании Walk With Giants, Johnnie Walker рассказывает истории успешных людей, показывая, какой путь они прошли для достижения успеха. В разных странах послами бренда были - Ричард Брэнсон, Льюс Хэмилтон, Николай Фоменко и многие другие, в Украине лицом кампании стал Александр Роднянский.'
                    }));
                }
            },
            blockmechanic: {
                title: 'Механика',
                callback: function(obj, event, key) {
                    obj.syncCode();
                    obj.insertHtml(customBlockHtml({
                        img: '/img/case_img2.png',
                        title: 'Механика',
                        content: 'Чтобы поддержать основную идею кампании - Keep Walking, мы разработали конкурс с простой механикой и подобрали мотивирующие призы - победитель конкурса получал обучение в Киево-Могилянской бизнес школе, а призеры наборы бизнес-книг от издательства Иванов, Манн и Фербер и виски Johnnie Walker Black Label.'
                    }));
                }
            },
            blocktask: {
                title: 'Задача',
                callback: function(obj, event, key) {
                    obj.syncCode();
                    obj.insertHtml(customBlockHtml({
                        img: '/img/case_img4.png',
                        title: 'Задача',
                        content: 'Используя таблицу интегралов элементарных функций, получим: первая производная стремительно искажает метод последовательных приближений, в итоге приходим к логическому противоречию. Прямоугольная матрица определяет предел последовательности, что несомненно приведет нас к истине. Точка перегиба, не вдаваясь в подробности, детерменирована. '
                    }));
                }
            },
            blocksolution: {
                title: 'Решение',
                callback: function(obj, event, key) {
                    obj.syncCode();
                    obj.insertHtml(customBlockHtml({
                        img: '/img/case_img5.png',
                        title: 'Решение',
                        content: 'Уравнение в частных производных решительно привлекает двойной интеграл, дальнейшие выкладки оставим студентам в качестве несложной домашней работы. Аффинное преобразование соответствует математический анализ, таким образом сбылась мечта идиота - утверждение полностью доказано. '
                    }));
                }
            },
            blockresults: {
                title: 'Результаты',
                callback: function(obj, event, key) {
                    obj.syncCode();
                    obj.insertHtml('<p class="case_block results_info min_width">'+
                    '<img alt="" src="img/case_results_logo.png"><span class="h1">Результаты кампании</span>'+
                    '<span class="txt">В конкурсе приняло участие 1500 человек,<br>а показатель Talking about this достиг отметки в 9142 Facebook-юзера.</span>'+
                    '</p><table class="results_info_table table" id="table'+ Math.floor(Math.random() * 99999)+'"><tbody><tr>'+
                    '<td><img alt="" src="img/case_res1.png"></td><td><img alt="" src="img/case_res2.png"></td>'+
                    '<td><img alt="" src="img/case_res3.png"></td><td><img alt="" src="img/case_res4.png"></td>'+
                    '</tr><tr><td>participants</td><td>fans on facebook<br>due to activity</td><td>talking<br>about this</td>'+
                    '<td>facebook<br>viral reach</td></tr></tbody></table>');
                    obj.observeTables();
                }
            },
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
//                console.log(item);
//                $(item).html($(item).getCode());
                $(item).val($(item).getCode());
            });
        }
        $('#data-form').submit();
        return true;
    });

</script>
{/literal}
