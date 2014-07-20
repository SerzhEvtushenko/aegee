{use_css "dev_tools/general.css"}
<div class="well">
    <h3 class="pull-left">{$structure.modules[$module]['title']}</h3>
    <a class="btn btn-small pull-left ml10" href="admin/{$module}/add/">
        <i class="icon-plus"></i>
        Добавить
    </a>
    <a class="btn btn-small pull-left ml10 disabled">
        <i class="icon-move"></i>
        Сортировать
    </a>
    <a class="btn btn-small pull-left ml10 disabled" >
        <i class="icon-cog"></i>
        Настройки
    </a>
    <a class="btn btn-small pull-left ml10 disabled">
        <i class="icon-download"></i>
        Экспорт
    </a>

    <div class="btn-group pull-right">
        <a class="btn btn-small disabled">
            <i class="icon-th-list"></i>
        </a>
        <a  class="btn btn-small active disabled">
            <i class="icon-list"></i>
        </a>
    </div>

    <div class="clearfix"></div>
</div>

<div class="container-fluid">
{if isset($module_structure['filter'])}
    <form class="form-inline pull-left" id="filter-form" method="post">
        {foreach from=$module_structure['filter'] key=filter_key item=filter_item}
        {if $filter_item.type == "select"}
            <select type="text" name="filter[{$filter_item['field']}]" onchange="$('#filter-form').submit();">
                <option value="---">показать все</option>
                {foreach from=$filter_item['field_values'] item=item}
                <option value="{$item.id}"{if $filter_item.value == $item.id} selected="selected" {/if}>{$item.title}</option>
                {/foreach}
            </select>

        {elseif $filter_item.type == "checkbox"}
            <label for="filter-{$filter_item['field']}">{$filter_item['title']}</label>
            <select id="filter-{$filter_item['field']}" type="text" name="filter[{$filter_item['field']}]" onchange="$('#filter-form').submit();">
                <option value="---">показать все</option>
                <option value="<1"{if $filter_item.value == "<1"} selected="selected" {/if}>Нет</option>
                <option value="1"{if $filter_item.value == "1"} selected="selected" {/if}>Да</option>
            </select>
        {else}
            <label>{$filter_item.title}</label>
            <input type="text" value="{$filter_item.value}" name="filter[{$filter_item['field']}]">
        {/if}
        {/foreach}
        <input type="submit" class="btn btn-small " value="Фильтр">
    </form>
    {*<div class="btn-group pull-right">*}
        {*<div class="btn active">Search</div>*}
        {*<div class="btn">*}
            {*<i class="icon-remove"></i>*}
        {*</div>*}
    {*</div>*}

    <div class="clearfix"></div>

{/if}
</div>

{admin_notifications}

{if !count($objects)}
    <div class="well">
        <a href="javascript:;" class="close">×</a>
        <h3>Нет данных для отображения</h3>
            <p>Вы можете создать новый объект!</p>
    </div>
{else}
<table class="table table-striped table-bordered table_list">
        <thead>
        <tr>
        {foreach from=$fields key=field_name item=field_info}
            <th id="field-title-{$field_name}">
                {$field_info.title}
                {if isset($sort) && ($sort == $field_name)}<i class="icon icon-arrow-up pull-right"></i>{/if}
                {if isset($sorted) && ($sorted == $field_name)}
                {/if}
            </th>
        {/foreach}
            <th style="width:100px;">Действия</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$objects item=object}
        <tr>
            {foreach from=$fields key=field_name item=field_info}
            <td class="{if isset($field_info.align)}{$field_info.align} {/if}table_cell_{$field_info.type} table_cell_{$field_name}">
                {table_cell_default $object[$field_name] $field_info}
                {if $field_name == "title" && $object.tags}
                <span class="label label-info pull-right">{$object.tags|implode:","}</span>
                {/if}
            </td>
            {/foreach}
            <td class="center">
                <div class="btn-group"{if isset($module_structure.sort_buttons) && $module_structure.sort_buttons} style="width: 150px" {/if}>
                    {if isset($module_structure.sort_buttons) && $module_structure.sort_buttons}
                        <a class="btn" href="admin/{$module}/moveup/{$object.id}/"><i class="icon-arrow-up"></i></a>
                        <a class="btn" href="admin/{$module}/movedown/{$object.id}/"><i class="icon-arrow-down"></i></a>
                    {/if}
                    <a class="btn" href="admin/{$module}/edit/{$object.id}"><i class="icon-pencil"></i></a>
                    <span class="btn confirm_delete" id="delete-{$object.id}"><i class="icon-remove"></i></span>
                </div>

                {*<a href="admin/{$module}/edit/{$object.id}"><i class="icon-pencil"></i></a>*}
                {*&nbsp;<i class="icon-remove confirm_delete" id="delete-{$object.id}" style="cursor: pointer;"></i>*}
            </td>
        </tr>
        {/foreach}

        </tbody>
    </table>
{/if}

<div class="pagination">

    {if (isset($pager.pages_count)) && ($pager.pages_count > 1) }
        <ul >
            {if 1 != $pager.current_page}
                {var cp2 = ($pager.current_page-1) }
                <li >
                    <a href="{$link}page/{$cp2}/" class="arrows_left">&laquo;</a>
                </li>
            {/if}
        {*------------------*}
            {if $pager.current_page<9}
                {for start=1 loop=min($pager.pages_count+1,10) value=i}
                    {if $i != $pager.current_page}
                        <li ><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {else}
                        <li class="active disabled"><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {/if}
                {/for}
                {if $pager.pages_count>9}
                    <li >
                        <a href="#">...</a>
                    </li>
                    <li >
                        <a href="{$link}page/{$pager.pages_count}/" >{$pager.pages_count}</a>
                    </li>
                {/if}
            {elseif $pager.current_page<($pager.pages_count-5)}
            asfasdf
                {if 1 == $pager.current_page}
                    <li class="active disabled">
                        <a href="{$link}page/1/">1</a>
                    </li>
                {else}
                    <li >
                        <a href="{$link}page/1/">1</a>
                    </li>
                {/if}
                <li >
                    <a href="#">...</a>
                </li>
                {for start=$pager.current_page-5 loop=($pager.current_page+6) value=i}
                    {if $i != $pager.current_page}
                        <li ><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {else}
                        <li class="active disabled"><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {/if}
                {/for}
                {if $pager.pages_count>9}
                    <li >
                        <a href="#">...</a>
                    </li>
                    <li >
                        <a href="{$link}page/{$pager.pages_count}/" >{$pager.pages_count}</a>
                    </li>
                {/if}
            {else}
                {if 1 == $pager.current_page}
                    <li class="active disabled">
                        <a href="{$link}page/1/">1</a>
                    </li>
                {else}
                    <li >
                        <a href="{$link}page/1/">1</a>
                    </li>
                {/if}

                <li >
                    <a href="#">...</a>
                </li>
                {for start=$pager.pages_count-9 loop=$pager.pages_count+1 value=i}
                    {if $i != $pager.current_page}
                        <li ><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {else}
                        <li class="active disabled"><a  href="{$link}page/{$i}/" >{$i}</a></li>
                    {/if}
                {/for}
            {/if}
        {*------------------*}
            {if $pager.current_page != $pager.pages_count}
                {var cp1 = ($pager.current_page+1) }
                <li >
                    <a href="{$link}page/{$cp1}/" class="arrows_right " >&raquo;</a>
                </li>
            {/if}
        </ul>
    {/if}
    </div>

    <div class="clearfix"></div>
<script type="text/javascript">
    var module_name = '{$module}';
</script>
{use_js "admin/list_module.js"}
