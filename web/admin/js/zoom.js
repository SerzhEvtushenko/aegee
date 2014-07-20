var def_zoom = 1;
var zoom_min = -20;
var zoom_max = 20;
var zoom_step_value = 0;
//var active_zoom = 1;

function initEditImg(zoom_slider, left, top, _zoom, zoom_dimensions){

    if (_zoom !== undefined) {
        active_zoom = _zoom;
    } else {
        active_zoom = def_zoom;
    }

    plus = zoom_slider.find('.plus');
    minus = zoom_slider.find('.minus');
    zoom_scrool_bar = zoom_slider.find('.zoom_slider');

    slider_img = zoom_slider.find('img');
    slider_img.css({
        let     : 0,
        top     : 0,
        width   : 'auto',
        height  : 'auto'
    });

    slider_img_property = 'width';
    slider_holder = zoom_slider.find('.page_photo');
    slider_holder._left = slider_holder.offset().left;
    slider_holder._top = slider_holder.offset().top;
    slider_holder._width = slider_holder.innerWidth();
    slider_holder._height = slider_holder.innerHeight();
    moving_img_shape = zoom_slider.find('.moving_img_shape');

    min_img_size = {};
    min_img_size.w = $(slider_holder).innerHeight();
    min_img_size.h = $(slider_holder).innerWidth();

    actual_img_size = {};
    actual_img_size.w = slider_img.outerWidth();
    actual_img_size.h = slider_img.outerHeight();

    min_i_size = 0;
    if(min_img_size.h > min_img_size.w) {
        min_i_size = min_img_size.h;
    } else {
        min_i_size = min_img_size.w;
    }

    act_i_size = 0;
    if(actual_img_size.h > actual_img_size.w) {
        act_i_size = actual_img_size.w;
    } else {
        act_i_size = actual_img_size.h;
        slider_img_property = 'height';
    }

    zoom_step_value = Math.pow(Math.E, Math.log( act_i_size / min_i_size ) / def_zoom / 6) ;
//    console.log(def_zoom, zoom_step_value);

    if(typeof left != 'undefined' && typeof top != 'undefined' && typeof _zoom != undefined){
//        var d_size = parseInt(parseFloat($(slider_img).css(slider_img_property))*Math.pow(zoom_step_value,active_zoom-def_zoom))
        var d_size = zoom_dimensions.split('x').pop();
        $(slider_img).css({
            left    : left,
            top     : top
        });
        if (d_size) {
            $(slider_img).css(slider_img_property, d_size + 'px');
        }
    }

    if(typeof updateDraggableOptions != "function"){
        function updateDraggableOptions(){
            // update width and height of draggable shape
            moving_img_shape.css({
                width:slider_img.innerWidth(),
                height:slider_img.innerHeight(),
                left: parseInt($(slider_img).css('left')),
                top:  parseInt($(slider_img).css('top'))
            });

            // update draggable area
            if (slider_holder._width-slider_img.innerWidth() <= 0) {
                var x1 = slider_holder._left + slider_holder._width-slider_img.innerWidth()
                var x2 = slider_holder._left
            } else {
                var x1 = slider_holder._left;
                var x2 = slider_holder._left + slider_holder._width-slider_img.innerWidth()
            }
            if (slider_holder._height-slider_img.innerHeight() <= 0) {
                var y1 = slider_holder._top + slider_holder._height-slider_img.innerHeight();
                var y2 = slider_holder._top;
            } else {
                var y1 = slider_holder._top;
                var y2 = slider_holder._top + slider_holder._height-slider_img.innerHeight();
            }
            img_moving_coord = [x1, y1, x2, y2];

            // if we make zoom out we must insure that img fully cover holder
//            console.log(parseFloat($(slider_img).css('left')), slider_holder._width , slider_img.innerWidth());
            if( parseFloat($(slider_img).css('left')) < slider_holder._width-slider_img.innerWidth() && slider_img.innerWidth() != 0){
//                $(slider_img).css('left',slider_holder._width-slider_img.innerWidth());
//                $(moving_img_shape).css('left',slider_holder._width-slider_img.innerWidth())
            }

            if( parseFloat($(slider_img).css('top')) < slider_holder._height-slider_img.innerHeight() && slider_img.innerHeight() != 0){
//                $(slider_img).css('top',slider_holder._height-slider_img.innerHeight());
//                $(moving_img_shape).css('top',slider_holder._height-slider_img.innerHeight())
            }

            $( moving_img_shape ).draggable("option", "containment", img_moving_coord);
        }
    }

    updateDraggableOptions();

    if(!$( moving_img_shape).hasClass('draggable_true') ){
        $( moving_img_shape).addClass('draggable_true');
        $( moving_img_shape ).draggable({
            containment: img_moving_coord,
            drag: function(event, ui){
                slider_img.css({
                    left:parseFloat($(this).css('left')),
                    top:parseFloat($(this).css('top'))
                })
            }
        });

        $( moving_img_shape ).resizable({
            aspectRatio: true,
            resize: function(event, ui){
                slider_img.css({
                    width:  $(this).width(),
                    height: $(this).height()
                })
            },
            stop: function() {
                updateDraggableOptions();
            }
        });
    }
    if(!$(zoom_scrool_bar).hasClass('slider_init')){
        $(zoom_scrool_bar).addClass('slider_init');
        slider = $(zoom_scrool_bar).slider({
            orientation: 'vertical',
            range: "max",
            min: zoom_min,
            max: zoom_max,
            value: active_zoom,
            step: 1,
            slide: function(event, ui){
                zoom(ui.value)
            }
        });
    } else {
        slider.slider('value',active_zoom);
    }

    if(typeof zoom != "function"){
        function zoom(new_zoom){
            active_zoom = new_zoom;
            slider.slider('value',active_zoom);
            $(slider_img).css(slider_img_property, act_i_size * Math.pow(zoom_step_value,active_zoom-def_zoom));
            updateDraggableOptions();
        };
    }


    if(!$(plus).hasClass('click_event')){
        $(plus).addClass('click_event');
        $(plus).click(function(){
            if( active_zoom < zoom_max ) zoom(active_zoom+1)
        });
    }

    if(!$(minus).hasClass('click_event')){
        $(minus).addClass('click_event');
        $(minus).click(function(){
            if( active_zoom > zoom_min ) zoom(active_zoom-1)
        });
    }
}

$(document).ready(function(){
    if (!document.getElementById('popup-photo-holder')) return;

    var img = $('#popup-photo-holder img');
    var fn = function(){
        initEditImg($('#popup-photo-holder'), main_image_left, main_image_top, main_image_zoom, main_image_zoom_dimensions);
    };
    (img.get(0).complete) ? fn() : img.load(fn);


    function getClearImageSRC(item) {
        var src = $(item).attr('src');
        if (src.indexOf('?') !== -1) {
            src = src.substring(0, src.indexOf('?'));
        }
        return src;
    }

    $('#apply-photo-changes').click(function(){
        var shape = $('#popup-photo-holder .moving_img_shape');
        var dimensions  = parseInt(shape.css('width')) + 'x' + parseInt(shape.css('height'));
        var img_left    = parseInt(shape.css('left'));
        var img_top     = parseInt(shape.css('top'));
        var item = $('#popup-image-source');
        var data = {
            'name': 'main_image',
            'link': getClearImageSRC(item),
            'dimensions': dimensions,
            'zoom': active_zoom,
            'left': img_left,
            'top':  img_top
        };
        $.post(
            base_url + 'admin/'+ module_url + '/save_program_main_image_dimensions/'+id_object+'/',
            data,
            function(response) {
                var b = $('#apply-photo-changes-message');
                b.css('color', response.res ? 'green' : 'red');
                b.text(response.message);
                b.fadeIn();
                setTimeout(function(){b.fadeOut()}, 2000);
            },
            'json'
        );
    });

    $('#popup-photo-upload-cancel').click(function(){
        $('#popup-photo-upload').addClass('none');
        $('#popup-photo-holder').removeClass('none');
    });

    $('#change-image').click(function(){
        $('#popup-photo-upload').removeClass('none');
        $('#popup-photo-holder').addClass('none');
    });
});