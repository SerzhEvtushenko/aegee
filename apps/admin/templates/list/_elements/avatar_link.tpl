<div class="controls">
    <input type="file" class="input-xxlarge" name="{$field_name}" id="data-{$field_name}" />
    {if $data[$field_name]}
        <span class="help-block" ><a href="{$data[$field_name]}" target="about:tabs"><img src="{$data[$field_name]}" height="100px"/></a></span>
    {else}
    <span class="help-block">фото не загружено</span>
    {/if}
</div>
