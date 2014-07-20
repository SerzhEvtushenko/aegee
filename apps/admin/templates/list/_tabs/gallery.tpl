{if !isset($data.id)}
<div class="well">
    Сохраните создаваемый объект перед тем как добавлять и редактировать галереи
</div>
{else}
{foreach from=$data.galleries item=gallery}
<div class="well">
    {*<h4 class="pull-left">{$gallery.title}</h4>(<span class="link" onclick="loadGalleryInfo({$gallery.id});">edit</span>)*}
    <div class="clearfix"></div>
    <br/>
    <form method="post" id="files-titles-form-{$gallery.id}">
        <input type="hidden" value="{$gallery.id}" name="gallery_id" />
        <ul class="thumbnails thumbnails_sortable" id="gallery-{$gallery.id}">
        {foreach from=$data['galleries_files'][$gallery.id] key=file_key item=file_info}
            <li class="span3">
                <a id="gallery-item-{$gallery.id}-{$file_info.id}" href="{$file_info.link}" class="thumbnail" target="about:tabs" {if isset($tab_info.zoom)} data-src="{$file_info.link}" data-left="{$file_info.crop_left}" data-top="{$file_info.crop_top}" data-zoom="{$file_info.crop_zoom}" data-zoom-dimensions="{$file_info.crop_zoom_dimensions}" data-item-id="{$file_info.id}" {/if}>
                    {if !empty($tab_info.preview_size_name)}
                        <img src="{$file_info.sizes[$tab_info.preview_size_name]['link']}" alt="" style="height:100px;">
                    {else}
                        <img src="{$file_info.link}" alt="" style="height:100px;">
                    {/if}
                </a>
                <a class="close delete_image_handler" href="admin/{$module}/delete-gallery-item/{$data.id}/?gallery_item_id={$file_info.id}">&times;</a>
                {*<input type="text" value="{$file_info.title}" name="gallery_items[{$file_info.id}][title]" />*}
                <input type="hidden" class="position" value="{$file_info._position}" name="gallery_items[{$file_info.id}][_position]" />

                {foreach from=$tab_info.custom_item_columns key=field_name item=title}
                <input type="text" value="{$file_info[$field_name]}" name="gallery_items[{$file_info.id}][{$field_name}]">
                {/foreach}
            </li>
        {/foreach}
        </ul>
    </form>
    <form method="post" enctype="multipart/form-data" class="files-add-form" id="files-add-form{$gallery.id}" action="{$baseUrl}admin/{$module}/plugin-control/">
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

{*<span class="btn btn-info" onclick="showAddGallery();">добавить галерею</span>*}

<div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Редактирование информации</h3>
    </div>
    <div class="modal-body">

        <form id="gallery-info" class="form form-horizontal" method="post" action="{$baseUrl}admin/{$module}/plugin-control/">
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
            {foreach from=$tab_info.custom_gallery_columns key=field_name item=title}
                <label class="control-label" for="input-{$field_name}">{$title}</label>
                <div class="controls">
                    <input type="text" id="input-{$field_name}" name="gallery[{$field_name}]" placeholder="">
                </div>
            {/foreach}
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
          base_url + 'admin/' + module_url + '/plugin-control/',
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

    function recountPosition(id_gallery){
        var li_array = $('#' + id_gallery + ' li');
        for (var item = 0; item <= li_array.length; item++){
            $($(li_array[item]).children('input.position')).val(item+1);
        }
    }

    $(".thumbnails_sortable" ).sortable({
        stop: function( event, ui ) {
            recountPosition( $(this).attr('id') );
        }
    });

</script>
{/literal}

{if isset($tab_info.zoom)}
{*zoom code goes here*}
    {var modal_width = ($tab_info.zoom.width + 100)}
    {var modal_margin_left = (($modal_width / 2) * -1)}
    {var modal_margin_top  = ((($tab_info.zoom.height + 140) / 2) * -1)}
    {var modal_body_height = ($tab_info.zoom.height + 10) }
    {*{var editor_width = }*}
    {*{var editor_height = }*}
<div class="modal" id="modal-gallery-image-zoom" tabindex="-1" role="dialog" aria-labelledby="modal-gallery-image-zoom-label" aria-hidden="true" style="display:none; width: {$modal_width}px; margin: {$modal_margin_top}px 0 0 {$modal_margin_left}px">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="modal-gallery-image-zoom-label">Image resizing</h3>
    </div>

    <div class="modal-body" style="height: {$modal_body_height}px;">
        <div class="edit_photo big" id="gallery-photo-holder">
            <div style="float: left; border: 1px solid white;">
                <div class="page_photo" style="width: {$tab_info.zoom.width}px; height: {$tab_info.zoom.height}px;{if isset($tab_info.zoom.background_color)} background-color: {$tab_info.zoom.background_color};{/if}">
                    <img class="img field_holder" src="" alt="" id="gallery-image-source" />
                    <div class="mask"></div>
                    <div class="moving_img_shape"></div>
                </div>
            </div>

            <div class="zoom_bar">
                <i class="minus"></i>
                <i class="plus"></i>
                <i class="zoom_slider"></i>
            </div>

            <div class="clear"></div>
        </div>
    </div>

    <div class="modal-footer">
        <span id="apply-gallery-photo-changes-message" style="float: left; line-height: 26px;"></span>
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button class="btn btn-primary" onclick="applyGalleryImageZoomChanges();">Apply</button>
    </div>
</div>
{use_js '!admin/js/zoom.js'}
{use_css '!admin/css/zoom.css'}
<script type="text/javascript">
    var preview_size_name = '{$tab_info.preview_size_name}';
    {literal}
    var current_editing_gallery_item_id = 0;
    $('#modal-gallery-image-zoom').on('hide',function(){
        current_editing_gallery_item_id = 0;
    });

    $('.thumbnail').click(function(evt){
        evt.preventDefault();

        var t = $(this);
        var left = t.data('left'), top = t.data('top'), zoom = t.data('zoom'),
                zoom_dimensions = t.data('zoomDimensions'), src = t.data('src');
        current_editing_gallery_item_id = t.data('itemId');

        $('#modal-gallery-image-zoom').modal('show');
        var img = $('#modal-gallery-image-zoom img')
        img.css('visibility','hidden');
        img.attr('src',src);
        var fn = function(){
            initEditImg($('#gallery-photo-holder'), left, top, zoom, zoom_dimensions);
            img.unbind('load');
            img.css('visibility','visible');
        };
        (img.get(0).complete) ? fn() : img.load(fn);

        return false;
    });

    var applyGalleryImageZoomChanges = function(){
        var shape = $('#gallery-photo-holder .moving_img_shape');
        var dimensions  = parseInt(shape.css('width')) + 'x' + parseInt(shape.css('height'));
        var img_left    = parseInt(shape.css('left'));
        var img_top     = parseInt(shape.css('top'));
        var item = $('#gallery-image-source');
        var data = {
            'id_item' : current_editing_gallery_item_id,
            'plugin_name' : 'gallery',
            'method'      : 'updateItemZoomDimensions',
            'id'          : id_object,
            'name': 'main_image',
            'dimensions': dimensions,
            'zoom': active_zoom,
            'left': img_left,
            'top':  img_top
        };

        $.post(
                base_url + 'admin/'+ module_url + '/plugin-control/',
                data,
                function(response) {
                    var b = $('#apply-gallery-photo-changes-message');
                    b.css('color', response.res ? 'green' : 'red');
                    b.text(response.message);
                    b.fadeIn();
                    setTimeout(function(){b.fadeOut()}, 2000);
                    if (response.res) {
                        $('[data-item-id='+current_editing_gallery_item_id+']').data({
                            'left' : response.item.crop_left,
                            'top'  : response.item.crop_top,
                            'zoom' : response.item.crop_zoom,
                            'zoomDimensions': response.item.crop_zoom_dimensions
                        });
                        $('[data-item-id='+current_editing_gallery_item_id+'] img').attr('src', response.item.sizes[preview_size_name].link+'?'+Math.random());
                    }
                },
                'json'
        );
    };
    {/literal}
</script>
{/if}
{/if}