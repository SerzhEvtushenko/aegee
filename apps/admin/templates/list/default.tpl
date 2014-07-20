<div class="row-fluid">
    {if isset($module_structure['filter'])}
        {include file="list/_filters.tpl"}
    {/if}

    {admin_notifications}

<div class="widget-box">
    <div class="widget-title">
        <span class="icon">
            <i class="icon-th"></i>
        </span>
        <h5>Дані</h5>
    </div>
    <div class="widget-content">
    {if !count($objects)}
        <h3>Нет данных для отображения</h3>
        <p>Вы можете создать новый объект!</p>
    {else}

        <a class="btn" href="admin/{$module}/add/" style="margin-bottom: 10px;">
            <i class="icon-plus"></i>
            Додати
        </a>
    <table class="table table-bordered table-striped with-check">
    <thead>
        <tr>
            {*<th>*}
                {*<input type="checkbox" id="title-table-checkbox" name="title-table-checkbox" />*}
            {*</th>*}
            {foreach from=$fields key=field_name item=field_info}
                <th id="field-title-{$field_name}">
                    {$field_info.title}
                    {if isset($sort) && ($sort == $field_name)}<i class="icon icon-arrow-up pull-right"></i>{/if}
                    {if isset($sorted) && ($sorted == $field_name)}
                    {/if}
                </th>
            {/foreach}
            <th></th>
        </tr>
    </thead>
    <tbody>
    {foreach from=$objects item=object}
        <tr>
            {*<td>*}
                {*<input type="checkbox" />*}
            {*</td>*}
            {foreach from=$fields key=field_name item=field_info}
            <td class="{if isset($field_info.align)}{$field_info.align} {/if}table_cell_{$field_info.type} table_cell_{$field_name}">
                {table_cell_default $object[$field_name] $field_info}
            </td>
            {/foreach}
            <td>
                <div class="btn-group">
                    {if isset($module_structure.sort_buttons) && $module_structure.sort_buttons}
                        <a class="btn btn-mini" href="admin/{$module}/moveup/{$object.id}/"><i class="icon-arrow-up"></i></a>
                        <a class="btn btn-mini" href="admin/{$module}/movedown/{$object.id}/"><i class="icon-arrow-down"></i></a>
                    {/if}

                    <a class="btn btn-mini" href="admin/{$module}/edit/{$object.id}/"><i class="icon-pencil"></i></a>
                    {if !isset($module_structure.deleted)}
                        <a class="btn btn-mini confirm_delete" id="delete-{$object.id}" href="#"><i class="icon-remove"></i></a>
                    {/if}
                </div>
            </td>
        </tr>
    {/foreach}
    </tbody>
    </table>
    {/if}

    <a class="btn" href="admin/{$module}/add/">
        <i class="icon-plus"></i>
        Додати
    </a>

    {if isset($structure.modules[$module]['actions']['export'])}
        <a class="btn" href="admin/{$module}/export/">
            <i class="icon-download-alt"></i>
            Скачать в XLS
        </a>
    {/if}


    {include file="list/_pagination.tpl"}
    </div>
</div>
</div>


<script type="text/javascript">
    var module_name = '{$module}';
</script>
{use_js "!admin/js/jquery.dataTables.min.js"}
{use_js "!admin/js/solve.tables.js"}
{use_js "!admin/js/bootstrap-datepicker.js"}
{use_js "!admin/js/solve.form_common.js"}
{use_js "!admin/js/list_module.js"}
