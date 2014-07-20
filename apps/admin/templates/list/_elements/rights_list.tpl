<div class="controls">
    {foreach from=$fields[$field_name]['field_values'] item=right}
        <div class="">
            <input id="right-{$right.id}" type="checkbox" name="data[rights][]" value="{$right.id}" {if is_array($data.rights) && in_array($right.id , $data.rights)} checked="checked" {/if} style="float: left; margin-right: 5px;"/>
            <label class="" for="right-{$right.id}">{$right.title}</label>
        </div>
    {/foreach}
</div>
