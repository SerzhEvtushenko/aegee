<form class="well form-search">
    <input type="text" class="input-medium search-query">
    <button type="submit" class="btn">Поиск</button>
</form>
<div class="well sidebar-nav">
    <ul class="nav nav-pills nav-stacked">
        <li class="nav-header">Основные разделы</li>
        <li {if $module == "dashboard"}class="active"{/if}><a href="admin/"><i class="icon-home"></i>&nbsp;Dashboard</a></li>
        {foreach from=$structure.modules key=key item=item}
            <li class="{if $module == $key} active{/if}">
                <a href="admin/{$key}/"><i class="icon-book"></i>&nbsp;{$item.title}{admin_badge $item}</a>
            </li>
        {/foreach}

        <li class="nav-header">Дополнительно</li>
        <li><a href="admin/users/"><i class="icon-user"></i>&nbsp;Пользователи</a></li>
        <li><a href="admin/settings/"><i class="icon-cog"></i>&nbsp;Настройки</a></li>
        <li><a href="admin/logout/"><i class="icon-off"></i>&nbsp;Выход</a></li>
    </ul>
</div><!--/.well -->