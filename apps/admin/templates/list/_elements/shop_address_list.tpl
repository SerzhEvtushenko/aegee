<div class="controls" style="min-height: 40px;">
{foreach from=$data[$field_name] item=address}
    <a href="admin/shop-addresses/edit/{$address.id}/">{$address.city_title}, {$address.address}</a><br/>
{/foreach}
    {if isset($data.id)}
    <a href="admin/shop-addresses/add/?id_shop={$data.id}">добавить адрес</a>
    {/if}
</div>