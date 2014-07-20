<div class="controls">
    <select type="text" class="input-xxlarge" name="data[{$field_name}][]" id="data-{$field_name}" multiple="true">
    {foreach from=$fields[$field_name]['field_values'] item=select_item}
        <option value="{$select_item.id}" {if isset($data[$field_name]) && is_object($data[$field_name]) && $data[$field_name]->idInCollection($select_item.id)}selected="selected"{/if}>{$select_item.title}</option>
    {/foreach}
    </select>
    <span class="help-inline invisible">Enter {$field_name} correct please</span>
</div>
