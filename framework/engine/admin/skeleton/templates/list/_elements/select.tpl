<div class="controls">
    <select type="text" class="input-xxlarge" name="data[{$field_name}]" id="data-{$field_name}">
        <option value="0">---</option>
    {foreach from=$fields[$field_name]['field_values'] item=select_item}
        {if isset($data[$field_name])}
            {if (!$field_info['is_object'] && ($data[$field_name] == $select_item.id))
            || ($field_info['is_object'] && isset($data[$field_name]->id) && ($data[$field_name]->id == $select_item.id))}
                selected="selected"
            {/if}
        {/if}>
        {if isset($field_info.field_title)} {$select_item[$field_info.field_title]} {else}{$select_item.title}{/if}
    {/foreach}
    </select>
    <span class="help-inline invisible">Enter {$field_name} correct please</span>
</div>
