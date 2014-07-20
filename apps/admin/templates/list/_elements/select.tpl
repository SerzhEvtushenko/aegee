<div class="controls">
    <select type="text" class="input-xxlarge" name="data[{$field_name}]" id="data-{$field_name}">
        <option value="0">---</option>
    {foreach from=$fields[$field_name]['field_values'] item=select_item}
        <option value="{$select_item.id}"
            {if (isset($_ftl.get[$field_name]) && $_ftl.get[$field_name] == $select_item.id) }
                selected="selected"
            {elseif isset($data[$field_name])}
                {if isset($field_info['raw']) && ($field_info['raw'])}}
                    {if ($data[$field_name]) && ($data[$field_name] == $select_item.id)} selected="selected" {/if}
                {elseif (!$field_info['is_object'] && ($data[$field_name] == $select_item.id))
                || ($field_info['is_object']
                    && (isset($data[$field_name]['id']))
                    && ($data[$field_name]['id'] == $select_item.id))}
                    selected="selected"
                {/if}
            {/if}>
            {if isset($field_info.field_title)} {$select_item[$field_info.field_title]} {else}{$select_item.title}{/if}
        </option>

    {/foreach}
    </select>
    <span class="help-inline invisible">Enter {$field_name} correct please</span>
</div>
