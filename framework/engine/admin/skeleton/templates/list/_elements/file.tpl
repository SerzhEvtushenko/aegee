<div class="controls">
    <input type="file" class="input-xxlarge" name="{$field_name}" id="data-{$field_name}" />
    {*<span class="help-inline invisible">Enter {$field_name} correct please</span>*}
    {if $data[$field_name]}
    <span class="help-block" ><a href="{$data[$field_name]["link"]}">просмотреть файл</a></span>
    {/if}
</div>
