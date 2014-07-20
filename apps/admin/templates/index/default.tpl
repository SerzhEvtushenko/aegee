<div class="row-fluid">
    <div class="span5 well">
        {if !isset($site_info) || empty($site_info)}
            <h3>Нет информации для отображения</h3>
        {else}
            <h3>Statistics</h3>
            <table class="table">
                {foreach from=$site_info key=key item=item}
                    <tr>
                        <td>{$key}</td>
                        <td><b>{$item}</b></td>
                    </tr>
                {/foreach}
            </table>
        {/if}
    </div>


</div>

{literal}
    <script type="text/javascript">
        $(".datepicker_on").datepicker({
            'dateFormat': 'yy-mm-dd'
        });

    </script>
{/literal}
