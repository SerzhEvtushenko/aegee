<div class="controls">

    <select type="text" class="input-xxlarge" name="data[id_category]" id="data-id_category">
        <option value="0">---</option>
        {foreach from=$data.categories item=item key=key}
            <option value="{$item.id}"
                {if $data['id_category'] == $item.id} selected="selected" {/if}
            >
            {$item.title}
            </option>
        {/foreach}
    </select>
    <span class="help-inline invisible">Enter {$field_name} correct please</span>
</div>
