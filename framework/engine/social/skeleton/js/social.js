var need_redirect = true;
var redirect_path = '/';

function socialLogin(key, id_fly, type){
    var width = $(window).width();
    var height = $(window).height();
    if ('fb' == key) {
        width = (width-650)/2;
        height = (height-400)/2;
        facebook_popup = window.open('/social/facebook/?id_fly='+id_fly+'?type='+type,"facebook_popup", 'width=650,height=400,left='+width+',top='+height+';');
    } else if('vk' == key) {
        width = (width-850)/2;
        height = (height-500)/2;
        vkontakte_popup = window.open('/social/vkontakte/?id_fly='+id_fly+'?type='+type,"vkontakte_popup", 'width=800,height=450,left='+width+',top='+height+';');
    } else{
        width = (width-650)/2;
        height = (height-400)/2;
        my_world_popup = window.open('/social/odnoklasniki/?id_fly='+id_fly+'?type='+type,"facebook_popup", 'width=650,height=400,left='+width+',top='+height+';');
    }
}

function socialLoginAcl(key){
    var width = $(window).width();
    var height = $(window).height();
    if ('fb' == key) {
        width = (width-650)/2;
        height = (height-400)/2;
        facebook_popup = window.open('/social/facebook_acl/',"facebook_popup", 'width=650,height=400,left='+width+',top='+height+';');
    } else if('vk' == key) {
        width = (width-850)/2;
        height = (height-500)/2;
        vkontakte_popup = window.open('/social/vkontakte_acl/',"vkontakte_popup", 'width=800,height=450,left='+width+',top='+height+';');
    } else {
        width = (width-650)/2;
        height = (height-400)/2;
        my_world_popup = window.open('/social/odnoklasniki_acl/',"facebook_popup", 'width=650,height=400,left='+width+',top='+height+';');
    }
}

function authentication(login_status, like_group_status,social_key,_redirect_path) {
    redirect_path = _redirect_path;
    if(('true' == login_status) && ('true' == like_group_status)) {
        if (need_redirect) {
            window.location.href = 'http://'+document.location.hostname+'/'+redirect_path+'/';
        } else {
            window.location.reload();
        }
    } else if('true' != like_group_status) {
//        showLikePopup();
    } else {
        return false;
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
    } else if('od' == social_key){
        url  = 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1';
        url  = 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=2&st.noresize=on';
        url += '&st.comments=' + encodeURIComponent(text);
        url += '&st._surl='    + encodeURIComponent(purl);
    } else if('fb' == social_key){
        url  = 'http://www.facebook.com/sharer.php?s=100';
        url += '&p[title]='     + encodeURIComponent(ptitle);
        url += '&p[summary]='   + encodeURIComponent(text);
        url += '&p[url]='       + encodeURIComponent(purl);
        url += '&p[images][0]=' + encodeURIComponent(pimg);
    } else if('tw' == social_key){
        url  = 'http://twitter.com/share?';
        url += 'text='      + encodeURIComponent(ptitle);
        url += '&url='      + encodeURIComponent(purl);
        url += '&counturl=' + encodeURIComponent(purl);
    } else if('od' == social_key){
        url  = 'http://connect.mail.ru/share?';
        url += 'url='          + encodeURIComponent(purl);
        url += '&title='       + encodeURIComponent(ptitle);
        url += '&description=' + encodeURIComponent(text);
        url += '&imageurl='    + encodeURIComponent(pimg);
    }

    var width = ($(window).width()-650)/2;
    var height = ($(window).height()-400)/2;
    return social_key_popup = window.open(url, social_key, 'width=650,height=400,left='+width+',top='+height+';');
}

function shareGroup(social_key, purl, ptitle, pimg, text){
    var _window = sharePopup(social_key, purl, ptitle, pimg, text);
    $(_window).onclose(function(){
		window.location.reload();
    });
}


(function($) {
    var window, timeout, onclose;
    var timeoutFn = function(){
        if (window[0].closed) {
            onclose();
            return;
        }
        timeout = setTimeout(timeoutFn, 100);
    };
    $.fn.onclose = function(fn) {
        if ($.isFunction(fn)) {
            window = $(this);
            onclose = fn;
            timeout = setTimeout(timeoutFn, 100);
        }
    }
})(jQuery);
