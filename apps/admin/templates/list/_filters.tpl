<div class="widget-box collapsible">
    <a data-toggle="collapse" href="#filters">
        <div class="widget-title">
            <span class="icon"><i class="icon-filter"></i></span>
            <h5>Filters</h5>
        </div>
    </a>
    <div id="filters" class="collapse">
        <div class="widget-content nopadding">
            <form class="form-horizontal" method="post" id="filter-form">
                {foreach from=$module_structure['filter'] key=filter_key item=filter_item}
                    <div class="control-group fleft">
                        <label class="control-label">{$filter_item.title}</label>
                        <div class="controls">
                            {if $filter_item.type == "select"}
                                <select name="filter[{$filter_item['field']}]" onchange="$('#filter-form').submit();">
                                    <option value="---">показать все</option>
                                    {foreach from=$filter_item['field_values'] item=item}
                                        <option value="{$item.id}"{if $filter_item.value == $item.id} selected="selected" {/if}>{$item.title}</option>
                                    {/foreach}
                                </select>
                            {elseif $filter_item.type == "checkbox"}
                                <select id="filter-{$filter_item['field']}" name="filter[{$filter_item['field']}]" onchange="$('#filter-form').submit();">
                                    <option value="---">показать все</option>
                                    <option value="<1"{if $filter_item.value == "<1"} selected="selected" {/if}>Нет</option>
                                    <option value="1"{if $filter_item.value == "1"} selected="selected" {/if}>Да</option>
                                </select>
                            {elseif $filter_item.type == "date"}
                                <input type="text" class="datepicker" value="{if !empty($filter_item.value)}{date $filter_item.value 'Y-m-d'}{/if}" name="filter[{$filter_item['field']}]" data-date-format="yyyy-mm-dd" {if !empty($filter_item.value)} data-date="{date $filter_item.value 'Y-m-d'}"{/if}>
                            {else}
                                <input type="text" value="{$filter_item.value}" name="filter[{$filter_item['field']}]">
                            {/if}
                        </div>
                    </div>
                {/foreach}
                <div class="clear"></div>
                <div class="control-group">
                    <div class="controls">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <button class="btn" type="submit" name="filter_reset" value="1">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{*<form class="form-inline pull-left" id="filter-form" method="post">
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
</form>*}
{*<div class="btn-group pull-right">*}
{*<div class="btn active">Search</div>*}
{*<div class="btn">*}
{*<i class="icon-remove"></i>*}
{*</div>*}
{*</div>*}

{*
<div class="clearfix"></div>
*}