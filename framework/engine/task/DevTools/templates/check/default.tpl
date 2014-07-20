<h2>Server Environment Check</h2>

<table>
    <tr>
        <th></th>
        <th></th>
        <th></th>
    </tr>
{foreach from=$params key=key item=rules}
    <tr colspan="3">
        <td><h3>{$key}</h3></td>
    </tr>
    {foreach from=$rules key=key item=item}
        <tr>
            <td>{$item.title}</td>
            <td><span style="padding:5px;">{$item.value}</span></td>
            <td>
                {if ($item.result == 'pass')}
                    <span class="label label-success">pass</span>
                    {else}
                {*{if $item.rule}<span class="c_gr">({$item.rule})</span>{/if}*}
                    <span class="label label-important">fail</span>
                    <span>({$item.result})</span>
                {/if}
            </td>
        </tr>
    {/foreach}

{/foreach}
</table>
<p>
    <a href="javascript:history.back();">back</a>
</p>