{foreach from=$data.galleries item=gallery}
<div class="well">
    <h4 class="pull-left">{$gallery.title}</h4>(<span class="link" onclick="loadGalleryInfo({$gallery.id});">edit</span>)
    <div class="clearfix"></div>
    <br/>
    <ul class="thumbnails">
        <form method="POST" id="files-titles-form-{$gallery.id}">
            <input type="hidden" value="{$gallery.id}" name="gallery_id" />
    {foreach from=$data['galleries_files'][$gallery.id] key=file_key item=file_info}
        <li class="span3">
            <a href="{$file_info.link}" class="thumbnail" target="about:tabs">
                <img src="{$file_info.sizes.preview.link}" alt="" style="height:100px;">
            </a>
            <a class="close delete_image_handler" href="admin/{$module}/delete_gallery_item/{$data.id}/?gallery_item_id={$file_info.id}">&times;</a>
            <input type="text" value="{$file_info.title}" name="gallery_titles[{$file_info.id}]" />
        </li>
    {/foreach}
        </form>
    </ul>
    <form method="POST" enctype="multipart/form-data" class="files-add-form" id="files-add-form{$gallery.id}" action="{$baseUrl}admin/{$module}/plugin_control/">
        <input type="hidden" name="id" value="{$data.id}" />
        <input type="hidden" name="id_gallery" value="{$gallery.id}" />
        <input type="hidden" name="plugin_name" value="gallery" />
        <input type="hidden" name="method" value="saveFiles" />
        <input type="file" class="btn" name="__gallery_{$gallery.id}[]" multiple="true"/><br/>
        <input type="button" class="btn" value="Загрузить" onclick="uploadFilesForGallery({$gallery.id});">
        <input type="button" class="btn" value="Сохранить изменения" onclick="saveTitlesForGallery({$gallery.id});">
    </form>

</div>
{/foreach}

<span class="btn btn-info" onclick="showAddGallery();">добавить галерею</span>

<div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Редактирование информации</h3>
    </div>
    <div class="modal-body">

        <form id="gallery-info" class="form form-horizontal" method="post" action="{$baseUrl}admin/{$module}/plugin_control/">
            <input type="hidden" class="hidden" name="plugin_name" value="gallery" />
            <input type="hidden" class="hidden" name="method" value="save" />
            <input type="hidden" class="hidden" name="id" value="{$data.id}" id="id-object"/>
            <input type="hidden" class="hidden" name="id_gallery" value="" id="id-gallery" />
            <div class="control-group">
                <label class="control-label" for="input-title">Название</label>
                <div class="controls">
                    <input type="text" id="input-title" name="gallery[title]" placeholder="">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input-description">Описание</label>
                <div class="controls">
                    <input type="text" id="input-description" name="gallery[description]" placeholder="">
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</button>
        <button class="btn btn-primary" onclick="saveGalleryInfo();">Сохранить</button>
    </div>
</div>
{literal}
<script type="text/javascript">
    $('#gallery-info').ajaxForm({
        'success': updateGalleryComplete
    });
    $('.files-add-form').ajaxForm({
        'success': updateFilesComplete
    });
    function showAddGallery() {
        $('#input-title').val("");
        $('#input-description').val("");
        $('#id-gallery').val(0);
        $('#myModal').modal('show');
    }
    function loadGalleryInfo(id) {
        $.post(
          base_url + 'admin/' + module_url + '/plugin_control/',
          {
              'plugin_name' : 'gallery',
              'method'      : 'loadInfo',
              'id_gallery'  : id,
              'id'          : $('#id-object').val()
          },
          onLoadGalleryInfo
        );
    }
    function onLoadGalleryInfo(resp) {
        resp = $.parseJSON(resp);
        if (resp.status == "ok") {
            for(var i in resp.fields) {
                if ($('#input-' + i).length) {
                    $('#input-' + i).val(resp.fields[i]);
                }
            }
            $('#id-gallery').val(resp.fields['id']);
            $('#myModal').modal('show');

        } else {
            alert('Error while loading info!');
        }
    }
    function saveGalleryInfo() {
        $('#gallery-info').submit();
    }
    function updateGalleryComplete(resp) {
        resp = $.parseJSON(resp);
        if (resp == "ok") {
            $('#myModal').modal('hide');
            window.location.reload();
        }
    }

    function uploadFilesForGallery(id) {
        $('#files-add-form'+id).submit();
    }

    function saveTitlesForGallery(id) {
        $('#files-titles-form-'+id).submit();
    }

    function updateFilesComplete(resp) {
        window.location.reload();
    }
</script>
{/literal}