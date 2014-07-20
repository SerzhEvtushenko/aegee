function trim(string){
    return string.replace(/(^\s+)|(\s+$)/g, "");
}

function setAutoRemove( element, def_val ) {
    if (!$(element)) return false;

    $(element).focus(function() {
        if ($(this).val() == def_val) {
            $(this).val('');
            $(element).removeClass('default');
        }
    });
    $(element).blur(function() {
        if (trim($(this).val()) == '') {
            $(this).val(def_val);
            $(element).addClass('default');
        }
    });
    if ($(element).val() == '') {
        $(element).val(def_val);
        $(element).addClass('default');
    }
    return $(element);
}

$(document).ready(function() {

    if ($('.sub_menu').length) {

        $('.sub_menu_holder').each(function(){
            $(this).data('height', $(this).height());
        });

        $('.sub_menu_holder').each(function(){
            if (!$(this).hasClass('active')){
                $(this).animate({height: 0});
            }
        });

        $('.sub_menu').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).next('.sub_menu_holder').css('display','block');
            if ($(this).hasClass('active')){
                $(this).removeClass('active');
                $(this).next('.sub_menu_holder').animate({height: 0});
            }else{
                $(this).addClass('active');
                $(this).next('.sub_menu_holder').animate({height: $(this).next('.sub_menu_holder').data('height')+"px"});
            }
        });
    }

});
