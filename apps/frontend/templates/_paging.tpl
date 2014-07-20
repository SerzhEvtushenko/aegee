{if isset($pager.pages_count) && ($pager.pages_count > 1)}

    <div class="pagination">
        {if 1 != $pager.current_page}
            {var cp2 = ($pager.current_page-1) }
            <a href="{$link__}{$pre_page_link}{$cp2}/"><i class="corner corner-left">Prev</i></a>
        {/if}

        {if $pager.current_page < 9}
            {for start=1 loop=min($pager.pages_count+1,10) value=i}
                
                {if $i != $pager.current_page}
                    <a href="{$link__}{$pre_page_link}{$i}/{$post_page_link}" >{$i}</a>
                {else}
                    <span class="act">{$i}</span>
                {/if}
                
            {/for}
            {if $pager.pages_count > 9}
                <span class="  ">...</span>
                {if $pager.pages_count != $pager.current_page}
                    <a href="{$link__}{$pre_page_link}{$pager.pages_count}/{$post_page_link}" >{$pager.pages_count}</a>
                {else}
                    <span class="act">{$pager.pages_count}</span>
                {/if}
            {/if}
        {elseif $pager.current_page<($pager.pages_count-5)}
                {if 1 != $pager.current_page}
                    <a href="{$link__}{$pre_page_link}1/{$post_page_link}" >1</a>
                {else}
                    <span class="act">1</span>
                {/if}
                <span class="  ">...</span>
            {for start=$pager.current_page-3 loop=($pager.current_page+5) value=i}
                {if $i != $pager.current_page}
                    <a href="{$link__}{$pre_page_link}{$i}/{$post_page_link}">{$i}</a>
                {else}
                    <span class="act">{$i}</span>
                {/if}
            {/for}
            {if $pager.pages_count > 9}
                {if $pager.pages_count > ($pager.current_page+5)}
                    <span >...</span>
                {/if}
                <a href="{$link__}{$pre_page_link}{$pager.pages_count}/{$post_page_link}" >{$pager.pages_count}</a>
            {/if}
        {else}
            {if 1 != $pager.current_page}
                <a href="{$link__}{$pre_page_link}1/{$post_page_link}" >1</a>
            {else}
                <span class="act">1</span>
            {/if}

            {var start=($pager.pages_count - 9)}
            {if $start != 1}
                <span class="  ">...</span>
            {/if}
            {var start=($pager.pages_count - 9)}
            {for start=$start loop=($pager.pages_count+1) value=i}
                {if $i != $pager.current_page}
                    <a href="{$link__}{$pre_page_link}{$i}/{$post_page_link}" >{$i}</a>
                {else}
                    <span class="act">{$i}</span>
                {/if}
            {/for}
        {/if}
        {if $pager.current_page != $pager.pages_count}
            {var cp1 = ($pager.current_page+1) }
            <a href="{$link__}page/{$cp1}/" ><i class="corner corner-right">Next</i></a>
        {/if}

    </div>
{/if}