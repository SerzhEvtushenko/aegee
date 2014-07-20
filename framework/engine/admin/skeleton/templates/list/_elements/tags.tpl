<div class="control-group{if isset($errors[$field_name])} error{/if}">
    <label class="control-label" for="data-{$field_name}">{$field_title}</label>
    <div class="controls">
        <select multiple="multiple" data-placeholder="Введите первые буквы" class="input-xlarge chzn-select" name="data[{$field_name}][]" id="data-{$field_name}">
        {foreach from=$fields[$field_name]['field_values'] item=select_item}
            <option value="{$select_item.title}" {if isset($data.tags) && in_array($select_item.title, $data.tags)}selected="selected"{/if}>{$select_item.title}</option>
        {/foreach}
        </select>
    </div>
</div>