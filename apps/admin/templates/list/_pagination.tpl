{if (isset($pager.pages_count)) && ($pager.pages_count > 1) }
<div class="pagination alternate">

    {if (isset($pager.pages_count)) && ($pager.pages_count > 1) }
    <ul >
        {if 1 != $pager.current_page}
            {var cp2 = ($pager.current_page-1) }
            <li >
                <a href="{$link}page/{$cp2}/" class="arrows_left">&laquo;</a>
            </li>
        {/if}
        {*------------------*}
            {if $pager.current_page<9}
                {for start=1 loop=min($pager.pages_count+1,10) value=i}
                    {if $i != $pager.current_page}
                        <li ><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {else}
                        <li class="active disabled"><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {/if}
                {/for}
                {if $pager.pages_count>9}
                    <li >
                        <a href="#">...</a>
                    </li>
                    <li >
                        <a href="{$link}page/{$pager.pages_count}/" >{$pager.pages_count}</a>
                    </li>
                {/if}
            {elseif $pager.current_page<($pager.pages_count-5)}
                {if 1 == $pager.current_page}
                    <li class="active disabled">
                        <a href="{$link}page/1/">1</a>
                    </li>
                {else}
                    <li >
                        <a href="{$link}page/1/">1</a>
                    </li>
                {/if}
                <li >
                    <a href="#">...</a>
                </li>
                {for start=$pager.current_page-5 loop=($pager.current_page+6) value=i}
                    {if $i != $pager.current_page}
                        <li ><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {else}
                        <li class="active disabled"><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {/if}
                {/for}
                {if $pager.pages_count>9}
                    <li >
                        <a href="#">...</a>
                    </li>
                    <li >
                        <a href="{$link}page/{$pager.pages_count}/" >{$pager.pages_count}</a>
                    </li>
                {/if}
            {else}
                {if 1 == $pager.current_page}
                    <li class="active disabled">
                        <a href="{$link}page/1/">1</a>
                    </li>
                {else}
                    <li >
                        <a href="{$link}page/1/">1</a>
                    </li>
                {/if}

                <li >
                    <a href="#">...</a>
                </li>
                {for start=$pager.pages_count-9 loop=$pager.pages_count+1 value=i}
                    {if $i != $pager.current_page}
                        <li ><a href="{$link}page/{$i}/" >{$i}</a></li>
                    {else}
                        <li class="active disabled"><a  href="{$link}page/{$i}/" >{$i}</a></li>
                    {/if}
                {/for}
            {/if}
        {*------------------*}
{if $pager.current_page != $pager.pages_count}
    {var cp1 = ($pager.current_page+1) }
<li >
<a href="{$link}page/{$cp1}/" class="arrows_right " >&raquo;</a>
</li>
{/if}
</ul>
{/if}
</div>
{/if}
