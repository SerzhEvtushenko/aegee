<div id="sidebar">
    <ul>
        <li{if $module == "dashboard"} class="active"{/if}>
            <a href="admin/"><i class="icon-home"></i><span>Dashboard</span></a>
        </li>
        {foreach from=$structure.modules key=key item=item}
            {if mb_strpos($key, 'requests') === 0 && isset($were_requests_menu_item) && $were_requests_menu_item}
                {continue}
            {/if}
            {if isset($item.hide_from_menu) && $item.hide_from_menu}{continue}{/if}
            <li {if $module == $key || (isset($item.active_menu_keys) && in_array($module, $item.active_menu_keys))} class="active"{/if}>
                {if !isset($item.sub_menu)}
                    <a href="admin/{$key}/"><i class="icon icon-book"></i><span>{$item.title}</span></a>
                {else}
                    <a href="#" class="sub_menu" ><i class="icon icon-book"></i><span>{$item.title}</span></a>
                    <ul class="sub_menu_holder {if isset($module_slug) && array_key_exists($module_slug, $item.sub_menu)} active{/if}">
                        {foreach from=$item.sub_menu item=it key=k}
                            <li {if isset($module_slug) && ($k == $module_slug)} class="active"{/if}>
                                <a href="admin/{$k}/" ><i class="icon  icon-th"></i><span>{$it.title}</span></a>
                            </li>
                        {/foreach}
                    </ul>
                {/if}
            </li>
            {if mb_strpos($key, 'requests') === 0}
                {var were_requests_menu_item = 1}
            {/if}
        {/foreach}
    </ul>
</div>

