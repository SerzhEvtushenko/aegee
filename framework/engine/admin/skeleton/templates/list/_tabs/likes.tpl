{if count($data.likes.day_likes) > 0}
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script>
        var likes_array = new Array();
        var tmp_arr = new Array('x');
        {foreach from=$data.likes.day_likes  key=key item=item iteration=i}
            {if 1 == $i}
                {foreach from=$item key=k item=it}
                    tmp_arr.push('{$k}');
                {/foreach}
                likes_array.push(tmp_arr);
            {/if}
            var tmp_arr = new Array();
            tmp_arr.push('{$key}');
            {foreach from=$item key=k item=it iteration=j}
                tmp_arr.push({$it});
            {/foreach}
                likes_array.push(tmp_arr);
        {/foreach}
        {literal}
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawVisualization);
            function drawVisualization() {
                var data = google.visualization.arrayToDataTable(likes_array);

                new google.visualization.LineChart(document.getElementById('visualization')).
                        draw(data, {curveType: "function",
                width: 900, height: 500,
                vAxis: {maxValue: 10}}
                );
            }
        {/literal}
    </script>
    <div id="visualization" style="width: 900px; height: 500px;"></div>
{/if}
    <form action="" method="post" name="form">
        <div>Фильтр по каналам голосования</div>

        <label class="inline"><input type="checkbox" name="likes_filter[likes]" {if isset($data.likes.likes_filter.likes)}checked="checked" {/if} value="likes">likes</label>
        <label class="inline"><input type="checkbox" name="likes_filter[vk]" {if isset($data.likes.likes_filter.vk)}checked="checked" {/if} value="vk">vk</label>
        <label class="inline"><input type="checkbox" name="likes_filter[fb]" {if isset($data.likes.likes_filter.fb)}checked="checked" {/if} value="fb">fb</label>
        <label class="inline"><input type="checkbox" name="likes_filter[sms]" {if isset($data.likes.likes_filter.sms)}checked="checked" {/if} value="sms">sms</label>
        <label class="inline"><input type="checkbox" name="likes_filter[cd]" {if isset($data.likes.likes_filter.cd)}checked="checked" {/if} value="cd">cd</label>

        <input type="submit" value="submit">
    </form>

<div class="userl_likes_list controls">
    <div class="control-group">
        <label class="control-label">
            {foreach from=$data.likes.likes_count_array key=key item=item iteration=i}
                Всего голосов c {$key}: {$item} |
                {if $i%4 == 0}
                    <br>
                {/if}
            {/foreach}
            {foreach from=$data.likes.codes_count item=item key=key}
                Всего кодов {$key}: {$item}
            {/foreach}
             {*Всего кодов 1-го типа: {$data.likes.codes_count.type1}| Всего кодов 2-го типа: {$data.likes.codes_count.type2}*}
        </label>
    </div>
    <table class="table-bordered table-striped" style="width: 100%">
        <thead>
            <tr>
                <th>№</th>
                <th>Ava</th>
                <th>social key</th>
                <th>Name|code|phone</th>
                <th>Total like count</th>
                <th>date</th>
            </tr>
        </thead>
        <tbody>

            {foreach from=$data.likes.likes item=user iteration=i}
                <tr>
                    <td style="width: 20px; text-align: center;">{$i}</td>
                    <td style="width: 70px; text-align: center;">
                        {if ('cd' != $user.social_key) && ('sms' != $user.social_key)}
                            <img src="{if isset($user.avatar_link)}{$user.avatar_link}{else}{$user.s_ava}{/if}" >
                        {/if}
                    </td>
                    {if 'vk' == $user.social_key}
                        <td>
                            VK
                        </td>
                        <td>
                            <a href="http://vk.com/id{if !empty($user.id_social)}{$user.id_social}{else}{$user.s_id_social}{/if}" target="_blank">{$user.name}</a>
                        </td>
                    {elseif 'cd' == $user.social_key}
                        <td>
                            CD
                        </td>
                        <td>
                            Код: {$user.code} | Тип кода: {$user.code_type}
                        </td>
                    {elseif 'sms' == $user.social_key}
                        <td>
                            SMS
                        </td>
                        <td>
                            Телефон: {$user.phone}
                        </td>
                    {else}
                        <td>
                            FB
                        </td>
                        <td>
                            <a href="http://www.facebook.com/profile.php?id={if !empty($user.id_social)}{$user.id_social}{else}{$user.s_id_social}{/if}" target="_blank">{$user.name}</a>
                        </td>
                    {/if}
                    <td>
                        {user_likes_count $user.id_user}
                    </td>
                    <td>
                        {$user._created_at}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>