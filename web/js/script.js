var duration = 400;
var visible_popup = '';
var document_scroll;


function trim(string){
    return string.replace(/(^\s+)|(\s+$)/g, "");
}

function setAutoRemoveFormElement( element, def_val, placeholder ) {
    if (!$(element)) return false;

    $(placeholder).click(function(){$(element).focus()});

    $(element).focus(function() {
        if ($(this).val() == def_val) {
            $(this).val('');
            $(placeholder).fadeOut(100);
        }
    });
    $(element).blur(function() {
        if (trim($(this).val()) == '') {
            $(this).val(def_val);
            $(placeholder).fadeIn(100);
        } else {
            $(placeholder).hide();
        }
    });

    if ($(element).val() == '') {
        $(element).val(def_val);
        $(placeholder).show();
    } else {
        $(placeholder).hide();
    }
    return $(element);
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

function initPopup(popup){
    var close       = popup.find('.close');
    close.click(hidePopup);
    $('#back').hide().removeClass('none').click(hidePopup);

    popup.hide();
}
function hidePopup(){
    $('#back').fadeOut();

    $('.popup.act').fadeOut(duration, function(){
        $('.popup_table').addClass('hide');
        $('.popup.act').removeClass('act');
        $('.wrapper').removeClass('wrapper_popup').css('top','auto');
        $('body').removeClass('body_scroll');
        $(window).scrollTop(document_scroll);
    });
}
function showPopup(popup){

    if ( $(popup).hasClass('act') ) return false;

    if( $('.popup_table').hasClass('hide') ){
        // no visible popup
        document_scroll = $(window).scrollTop();
        $('body').addClass('body_scroll');
        $('.wrapper').addClass('wrapper_popup');
        $('.wrapper').css('top',-document_scroll);
        $('.popup_table').removeClass('hide');
        $(popup).fadeIn(duration);
        $('#back').fadeIn(duration);
    } else {
        // we have active popup
        $('.popup.act').fadeOut(duration, function(){
            $(popup).fadeIn(duration);
        });
        $('.popup.act').removeClass('act');
    }

    $(popup).addClass('act');

}


function initIndexSlider(slider){
    var act = 0;
    var toggles = $(slider.find('.toggles'));
    var items = slider.find('.item');
    var items_txt = slider.find('.item p');

    items.hide().removeClass('none');
    $(items[act]).show();
    items_txt.css('marginLeft','20px').hide();
    $(items_txt[act]).css('marginLeft',0).show();

    var dots_html = '';
    for(var i=0;i<items.length; i++){
        dots_html += '<i class="dot"></i>'
    }
    toggles.html(dots_html);
    var dots = slider.find('.dot');
    $(dots[act]).addClass('act');

    var sliderEvent = function(){
        if( $(this).hasClass('act') ) return false;

        removeSliderEvent();
        var next = act;

        if( $(this).hasClass('dot') ){
            next = dots.index(this);
        } else {
            next = (act+1 > items.length-1)?0:act+1;
        }

        $(items_txt[act]).animate({marginLeft:20,opacity:'toggle'},duration,function(){
            $(items[act]).fadeOut(duration);
            $(dots[act]).removeClass('act');
            act = next;
            $(dots[act]).addClass('act');
            $(items[act]).fadeIn(duration,function(){
                $(items_txt[act]).animate({marginLeft:0,opacity:'toggle'},duration,addSliderEvent);
            });
        });
    };

    var addSliderEvent = function(){
        dots.bind('click',sliderEvent);
    };
    var removeSliderEvent = function(){
        dots.unbind('click',sliderEvent);
    };

    addSliderEvent();
}

function _trackEvent(category, action, label){
    if(undefined != window._gaq){
        if (undefined != label) {
            _gaq.push(['_trackEvent', category, action, label]);
        } else {
            _gaq.push(['_trackEvent', category, action]);
        }
    }
}

function sharePopup(social_key, purl, ptitle, pimg, text){
    if ('vk' == social_key){
        url  = 'http://vkontakte.ru/share.php?';
        url += 'url='          + encodeURIComponent(purl);
        url += '&title='       + encodeURIComponent(ptitle);
        url += '&description=' + encodeURIComponent(text);
        url += '&image='       + encodeURIComponent(pimg);
        url += '&noparse=true';
        _trackEvent('Social', 'ClickShareVK', 'ShareVK');
    } else if('od' == social_key){
        url  = 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1';
        url += '&st._surl='    + purl;
        _trackEvent('Social', 'ClickShareOK', 'ShareOK');
    } else if('fb' == social_key){
        url  = 'http://www.facebook.com/sharer.php';
        url += '?s=100';
        url += '&p[title]='     + encodeURIComponent(ptitle);
        url += '&p[summary]='   + encodeURIComponent(text);
        url += '&p[url]='       + encodeURIComponent(purl);
        url += '&p[images][0]=' + encodeURIComponent(pimg);
        _trackEvent('Social', 'ClickShareFB', 'ShareFB');
    } else if('tw' == social_key){
        url  = 'http://twitter.com/share?';
        url += 'text='      + encodeURIComponent(ptitle);
        url += '&url='      + encodeURIComponent(purl);
        url += '&counturl=' + encodeURIComponent(purl);
    }

    var width = ($(window).width()-650)/2;
    var height = ($(window).height()-400)/2;
    return social_key_popup = window.open(url, social_key, 'width=650,height=400,left='+width+',top='+height+';');
}

function initForm(){
    $('.js-form').each(function(i,e){
        ajaxForm($(e), function(result){
            $(e).find('.form_element').removeClass('error');
            if (undefined != result.errors){
                for(var i in result.errors){
                    $(e).find('.form_'+i).addClass('error');
                }
            }
            if (undefined != result.message){
                showSuccess(result.message);
            }
        })
    });
}

function initAvatarForm(){
    $('.js-avatar-form').each(function(i,e){
        ajaxForm($(e), function(result){
            $(e).find('.form_element').removeClass('error');
            if (undefined != result.errors){

            } else {
                $('.js-avatar-form img').attr('src',result);

            }

        })
    });
}

var showSuccess = function(t) {
    $.gritter.add({
        title:    t,
        text:   '  '
    });
};

$(document).ready(function() {

    // init popups
    if( $('.popup').length ){
        $('.popup').each(function(index, item){
            initPopup($(item));
        });

        if( visible_popup.length ){
            showPopup($('.'+visible_popup));
        }
    }
    window.ajaxForm = function($form, success, before_send) {
        if ($form.data('form-initialized')) return;
        $form.data('form-initialized', true);
        $form.click(function(){
            $(this).find('.general_error').text('');
        });

        $form.find('input,textarea').focus(function(){
            $(this).closest('.input_holder').removeClass('error');
            $(this).closest('fieldset').find('.form_text.error').remove();
        });

        var beforeSend = before_send;
        var ajax_active = false;
        var submitRequest = function() {
            if (ajax_active) return;
            if (beforeSend) {
                var before = beforeSend();
                if (before === false) return;
            }
            ajax_active = true;
            $form.css('opacity',0.5);
            $form.find('.input_holder').removeClass('error');
            $form.find('.form_text.error').remove();
            var data = $form.serialize();
            $.ajax({
                'url': $form.attr('action'),
                'dataType': 'json',
                'type': 'post',
                'data': data
            }).done(success)
                .fail(function(){
                }).always(function(){
                    ajax_active = false;
                    $form.css('opacity', 1);
                });
        };

        $form.find('input').keydown(function(e){
            if (e.keyCode == 13) submitRequest();
        });

        $form.submit(function(e){
            e.preventDefault();
            e.stopPropagation();
            submitRequest();
            return false;
        });
    };
    if ($('.js-form').length) {
        initForm();
    }

    // placeholder for inputs
    if( $('.placeholder').length ){
        setTimeout(function(){
            $('.placeholder').each(function(i, e){
                if( $(e).siblings('input').length ){
                    setAutoRemoveFormElement($(e).siblings('input'), '', $(e));
                } else {
                    setAutoRemoveFormElement($(e).siblings('.input-holder').find('textarea'), '', $(e));
                }
            });
        },100);
    }

    if ($('#acl_send_login_request').length){
        var flag_login = 1;
        $('#acl_send_login_request').click(function(){
            if (flag_login){
                flag_login = 0;
                var data = {email: $('#acl_email').val(), 'password': $('#acl_password').val()};
                $('.form_login .input_holder').removeClass('error');
                $('.form_login .text').addClass('none');

                $.post('profile/login/', {data: data}, function(result) {
                    if ('true' == result) {
                        window.location.href = window.location.origin+'/'+$('#current-language').val()+'/';
                    } else {
                        $('.form_login .input_holder').addClass('error');
                        $('.form_login .text').removeClass('none');

                    }
                    flag_login = 1;
                });
            }
        })
    }

    if ($('#registration').length) {
        var flag_registered = 1;
        $('#registration').click(function(){
            if (flag_registered) {
                flag_registered = 0;

                var data = new Object();

                $('.form_registration input[type=text]').each(function(){
                    data[$(this).data('field')] = $(this).val();
                });
                $('.form_registration input[type=password]').each(function(){
                    data[$(this).data('field')] = $(this).val();
                });

                $('.form_registration textarea').each(function(){
                    data[$(this).data('field')] = $(this).val();
                });

                $.post('profile/save-new-user/', {data: data}, function(result) {
                    var res = jQuery.parseJSON(result);
                    $('.form_registration .input_holder').removeClass('error');
                    $('.form_registration .text').addClass('none');

                    if (true == res.status){
                        window.location.href = window.location.protocol+'//'+window.location.hostname+'/profile/';
                    }else if (undefined != res.errors){
                        for(i in res.errors){
                            $('.form_registration .'+i+' .input_holder').addClass('error');
                            $('.form_registration .'+i+' .text').removeClass('none');
                        }
                    }
                    flag_registered = 1;
                });
            }
        });
    }

    if ($('#update-user').length) {
        var flag_registered = 1;
        $('#update-user').click(function(){
            if (flag_registered) {
                flag_registered = 0;

                var data = new Object();

                $('.form_registration input[type=text]').each(function(){
                    data[$(this).data('field')] = $(this).val();
                });
                $('.form_registration input[type=password]').each(function(){
                    data[$(this).data('field')] = $(this).val();
                });

                $('.form_registration textarea').each(function(){
                    data[$(this).data('field')] = $(this).val();
                });
                data['sex'] = $('.form_registration input[type=radio][checked=checked]').val();

                $.post('profile/update-user-info/', {data: data}, function(result) {
                    var res = jQuery.parseJSON(result);
                    $('.form_registration .input_holder').removeClass('error');
                    $('.form_registration .text').addClass('none');

                    if (true == res.status){
//                        window.location.href = window.location.protocol+'//'+window.location.hostname+'/profile/';
                        showSuccess(res.message);
                    }else if (undefined != res.errors){
                        for(i in res.errors){
                            $('.form_registration .'+i+' .input_holder').addClass('error');
                            $('.form_registration .'+i+' .text').removeClass('none');
                        }
                    }
                    flag_registered = 1;
                });
            }
        });
    }


    if ($('#send-feedback').length) {
        var flag_feedback = 1;
        $('#send-feedback').click(function(){
            if (flag_feedback) {
                flag_feedback = 0;
                var data = {name: $('.feedback_popup .name input').val(),
                            email: $('.feedback_popup .email input').val(),
                            content: $('.feedback_popup .description textarea').val()};

                $('.feedback_popup .input_holder').removeClass('error');

                $.post('save-feedback/', {data: data}, function(result) {
                    var res = jQuery.parseJSON(result);

                    if (true == res.status) {
                        showPopup('.feedback_popup_success');
                        setTimeout(function(){
                            hidePopup();
                        }, 3000);
                    } else {
                        if (undefined != res.errors){
                            if (undefined != res.errors.name){
                                $('.feedback_popup .name .input_holder').addClass('error');
                            }
                            if (undefined != res.errors.email){
                                $('.feedback_popup .email .input_holder').addClass('error');
                            }
                            if (undefined != res.errors.content){
                                $('.feedback_popup .description .input_holder').addClass('error');
                            }
                        }

                    }
                    flag_feedback = 1;
                });
            }
        });
    }

    if( $('.login_popup_btn').length ){
        $('.login_popup_btn').click(function(e){
            e.preventDefault();
            if( $('.login_popup').hasClass('act') ){
                $('.login_popup').removeClass('act');
            } else {
                $('.login_popup').addClass('act');
            }
        });
    }

    if ($('.phone_mask').length){
        $('.phone_mask').mask("(999) 999-99-99");
    }
    if ($('.birthday').length){
        $('.birthday').mask("99 99 9999");
    }


    $('.show_feedback_js').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        showPopup('.feedback_popup')
    });

    if( $('.index_slider').length ){
        initIndexSlider($('.index_slider'));
    }

    // аккордион для вопросы ответы
    function initQuestionAccordion(){
        var act_el_class = document.hash;
        var accordion = $('.faq_accordion');
        var titles = $(accordion).find('.faq_title');
        var contents = $(accordion).find('.faq_content');
        var act_el;
        if(act_el_class){
            act_el = titles.index($('.'+act_el_class)[0]);
        }

        $(contents).each(function(index, item){
            if(index == act_el){
                var content_height = $($(item).find('.faq_content_inner')[0]).innerHeight();
                $(item).css({height:content_height});
                $(titles[index]).addClass('act');
            } else {
                $(item).css({height:0});
                $(titles[index]).removeClass('act');
            }
        });

        $(titles).each(function(index, item){
            $(item).click(function(){
                var index_ = titles.index(this);

                if($(this).hasClass('act')){
                    $(this).removeClass('act');
                    $(contents[index_]).animate({height:0},duration);
                } else {
                    var content_height = $($(contents[index_]).find('.faq_content_inner')[0]).innerHeight();
                    $(this).addClass('act');
                    $(contents[index_]).animate({height:content_height},duration);
                }
            })
        })
    }

    if($('.faq_accordion').length) initQuestionAccordion();


//    if($('.js-avatar-form').length) {
//        initAvatarForm();
//        $('.user_avatar').change(function(){
//            $('.js-avatar-form').submit();
//        })
//    }


    if ($('#password-recovery').length) {
        var flag = 1;
        $('#password-recovery').click(function(){
            if (flag) {
                flag = 0;
                var email = $('.password_recovery_popup .form input').val();
                if (email.length > 0 ) {
                    $.post('profile/password-recovery/', {email: email}, function(result) {

                        $('.password_recovery_popup .input_holder').removeClass('error');
                        $('.password_recovery_popup .text').addClass('none');

                        if ('true' == result){
                            showPopup('.password_recovery_success');
                            setTimeout(function(){
                                hidePopup();
                            }, 5000);
                        }else if ('"invalid_email"' == result){
                            $('.password_recovery_popup .input_holder').addClass('error');
                            $('.password_recovery_popup .text').removeClass('none');

                        }else{
//                        window.location.reload();
                        }
                        flag = 1;
                    });
                } else {
                    $('.password_recovery_popup .input_holder').addClass('error');
                    flag = 1;
                }

            }

        });
    }

    if ($('.password_recovery_show_popup').length) {
        $('.password_recovery_show_popup').click(function(){
            showPopup('.password_recovery_popup');
        })
    }

    var profileAvatarForm = $('#profile-avatar-form');
    if (profileAvatarForm.length) {
        profileAvatarForm.ajaxForm({
            dataType: 'json',
            beforeSubmit: function() {
                profileAvatarForm.css('opacity',0.5);
            },
            success: function(response) {
                $('img[data-role="avatar"]').attr('src',response);
                profileAvatarForm.css('opacity',1);
            },
            error: function(res){
                profileAvatarForm.css('opacity',1);
            }
        });
        profileAvatarForm.find('input').change(function(e){
            profileAvatarForm.submit();
        });
    }

    if( $(".fancybox").length ){
        $(".fancybox").fancybox({
            openEffect	: 'none',
            closeEffect	: 'none'
        });
    }


    if ($('.js_change_photo').length) {
        if ($('.js_change_photo').data('src').length > 0) {
            $('.js_change_photo').mouseenter(function(){
                $(this).attr("src",$(this).data('src'));
            });
            $('.js_change_photo').mouseleave(function(){
                $(this).attr("src",$(this).data('basesrc'));
            });
        }
    }
    if ($('.changePassword').length){
        $('.changePassword').click(function(){
            if ($('.password-container').hasClass('act')) {
                $('.password-container').animate({
                    'height': 0
                }, 500);
            } else {
                $('.password-container').animate({
                    'height': 180
                }, 500);
            }

        })
    }

    if ($('#newUserPopup').length) {
        showPopup($('.new-user-popup'));
//        setTimeout(function(){
//            hidePopup();
//        }, 2500)
    }

});