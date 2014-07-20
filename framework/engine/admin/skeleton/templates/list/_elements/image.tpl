<div class="controls">
    <input type="file" class="input-xxlarge" name="{$field_name}" id="data-{$field_name}" />
    {if $data[$field_name]}
        <ul class="thumbnails">
            <li class="span4">
                <a class="thumbnail" href="{$data[$field_name]["link"]}" target="about:tabs"><img src="{$data[$field_name]["link"]}" height="100px"/></a>
                <a class="close delete_image_handler" href="admin/{$module}/delete_image/{$data.id}/?alias={$field_name}&filename={$data[$field_name]["link"]}">&times;</a>
            </li>
        </ul>
    {/if}
    <div>{$field_info.description}</div>
</div>
