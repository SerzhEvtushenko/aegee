window.onload = function() {
    if (document.getElementById('dev-tools-wrapper')) {

        document.getElementById('dev-tools-wrapper').onclick = ffDevTools.showContainer;

        document.getElementById('dev-tools-close-button').onclick = ffDevTools.closeContainer;
//        document.getElementById('dev-tools-bar').onclick = closeDevTools;

        document.getElementById('switcher-cp').onclick = ffDevTools.activateTab;
        document.getElementById('switcher-db').onclick = ffDevTools.activateTab;
        document.getElementById('switcher-log').onclick = ffDevTools.activateTab;
        document.getElementById('switcher-console').onclick = ffDevTools.activateTab;

        document.getElementById('console-input').onkeyup = function(evt) {
            if (!(e = ffDevTools.getKeyCode(evt))) return true;

            if (e == 27) {
                document.getElementById('console-input').value = '';
            } else if (e == 37) {
                //left
            } else if (e == 38) {
                //up
                if (ffDevTools.console_history.isLast() && ffDevTools.console_history.getLast() != document.getElementById('console-input').value) {
                    document.getElementById('console-input').value = ffDevTools.console_history.getLast();
                } else {
                    document.getElementById('console-input').value = ffDevTools.console_history.getPrev();
                }
            } else if (e == 39) {
                //right
            } else if (e == 40) {
                //down
                if (ffDevTools.console_history.isLast()) {
                    if (ffDevTools.console_history.getLast() == document.getElementById('console-input').value) {
                        document.getElementById('console-input').value = '';
                    }
                } else {
                    document.getElementById('console-input').value = ffDevTools.console_history.getNext();
                }
            } else if (e == 13) {
                var value = document.getElementById('console-input').value;
                document.getElementById('console-input').value = '';
                document.getElementById('console-display').innerHTML += '> '+value+'<br/>';
                ffDevTools.console_history.add(value);

                if (value == 'clear') {
                    document.getElementById('console-display').innerHTML = '';
                    return true;
                }

                url = ffDevTools.parseCL(value);
                /*url = '/console/' + value
                        .replace(/(^\s+)|(\s+$)/g, "")
                        .replace(' ', '/')
                        .replace(':', '/')
                        .replace(/--([a-z]+)/g, '$1,/')
                        .replace('=', ',');*/
                ffDevTools.xhr.send(url, function(res) {
                    code = '<div style="color:#888;">'+res + '</div>';
                    document.getElementById('console-display').innerHTML += code;
                    document.getElementById('console-display').scrollTop = document.getElementById('console-display').scrollHeight;
                });
            }
        }
        var tab_id;
        if (tab_id = getCookie('ffdev_container_state')) {
            ffDevTools.showContainer();
            ffDevTools.activateTab(tab_id ? tab_id : 'switcher-console');
//        } else {
//            ffDevTools.activateTab({target:{id:'switcher-console'}});
        }
    }
}

var ffDevTools = {

    showContainer: function() {
        document.getElementById('dev-tools-bar').style.display = 'block';
        document.getElementById('dev-tools-wrapper').style.display = 'none';
        document.getElementById('console-input').focus();
        setCookie('ffdev_container_state', 'open');
    },

    closeContainer :function() {
        document.getElementById('dev-tools-bar').style.display = 'none';
        document.getElementById('dev-tools-wrapper').style.display = 'block';
        cleanCookie('ffdev_container_state');
    },

    activateTab: function(evt) {
        var ids = ['cp', 'db', 'log', 'console'];
        var id = typeof(evt) == 'object' ? evt.target.id.split('-').pop() : evt;
        for(var i=0,l=ids.length; i<l; i++) {
            document.getElementById('container-'+ids[i]).style.display = 'none';
            document.getElementById('switcher-'+ids[i]).style.color = '#666';
        }
        document.getElementById('container-'+id).style.display = 'block';
        document.getElementById('switcher-'+id).style.color = '#999';
        setCookie('ffdev_container_state', id);
    },


    getKeyCode: function(e) {
        if( !e ) {
            if( window.event ) {
                e = window.event;
            } else {
                return null;
            }
        }
        if( typeof( e.keyCode ) == 'number'  ) {
            e = e.keyCode;
        } else if( typeof( e.which ) == 'number' ) {
            e = e.which;
        } else if( typeof( e.charCode ) == 'number'  ) {
            e = e.charCode;
        } else {
            return null;
        }
        return e;
    },

    parseCL: function(value){
        var index = 0;
        var matches = [''];
        var need_space = false;
        var need_quote = false;
        for (var i=0; i < value.length; i++){
            var char = value.charAt(i);
            if (char != ' '){
                if (char == '"' && value.charAt(i-1) != '\\') {
                    if (need_quote) {
                        need_space = true;
                        need_quote = false;
                    } else {
                        need_space = false;
                        need_quote = true;
                    }
                } else {
                    if (!need_quote) {
                        need_space = true;
                    }
                    if (char=='\\' && value.charAt(i+1) == '"'){
                        char = '';
                    }
                    matches[index] += char;
                }
            } else {
                if (need_space) {
                    index += 1;
                    matches[index] = '';
                    need_space = false
                } else {
                    matches[index] += ' ';
                }
            }
        }
        var url = '/console/'+matches[0].replace(':','/');
        //console.log(matches);
        if (matches.length > 1) {
            url += '/?';
        }
        var index = 0;
        for(var i=1; i < matches.length; i++) {
            if (matches[i].charAt(0) == '-' && matches[i].charAt(1) == '-'){
                if (matches[i].indexOf('=') > 0) {
                    var tmp = matches[i].substr(2).split('=');
                    url += 'console_keys['+tmp[0]+']='+tmp[1]+'&';
                } else {
                    url += 'console_keys[' + matches[i].substr(2) + ']=1&';
                }
            } else if (matches[i].charAt(0) == '-' && matches[i].indexOf('=') > 0){
                url += matches[i].substr(1)+'&';
            } else {
                url += 'params['+index+']='+matches[i]+'&';
                index++;
            }
        }
        return url;
    }

}

ffDevTools.xhr = {

    XMLHttpFactories: [
        function () {return new XMLHttpRequest()},
        function () {return new ActiveXObject("Msxml2.XMLHTTP")},
        function () {return new ActiveXObject("Msxml3.XMLHTTP")},
        function () {return new ActiveXObject("Microsoft.XMLHTTP")}
    ],

    createXMLHTTPObject: function() {
        var xmlhttp = false;
        for (var i=0;i<ffDevTools.xhr.XMLHttpFactories.length;i++) {
            try {
                xmlhttp = ffDevTools.xhr.XMLHttpFactories[i]();
            }
            catch (e) {
                continue;
            }
            break;
        }
        return xmlhttp;
    },

    send: function(url, callback, data, method) {
        var req = ffDevTools.xhr.createXMLHTTPObject();
        if (!req) return;
        if (!method) method = 'POST';

        req.open(method, url, true);
        req.setRequestHeader('User-Agent','XMLHTTP/1.0');
        req.setRequestHeader('X_REQUESTED_WITH', 'XMLHttpRequest');
        if (data)
            req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
            req.onreadystatechange = function () {
                if (req.readyState != 4) return;
                if (req.status != 200 && req.status != 304) {
                    return;
                }
                callback(req.responseText);
            }
        if (req.readyState == 4) return;
        req.send(data);
    }

}

ffDevTools.console_history = {

    history: [],
    index: -1,

    add: function(value){
        if (this.history[this.history.length -1 ] == value || value == '') return false;
        this.history.push(value);
        this.index = this.history.length-1;
    },

    getLast: function(){
        if (this.history.length == 0){
            return '';
        } else {
            return this.history[this.history.length-1];
        }
    },

    isLast: function(){
        if (this.history.length == 0){
            return false;
        } else {
            return this.index == this.history.length-1;
        }
    },

    getPrev: function(){
        if (this.history.length == 0){
            return '';
        } else {
            if (this.index > 0){
                this.index--;
            }
            var value = this.history[this.index];
            return value;
        }
    },

    getNext: function(){
        if (this.history.length == 0){
            return '';
        } else {
            if (this.index < this.history.length - 1){
                this.index++;
            }
            var value = this.history[this.index];
            return value;
        }
    }
};

Array.prototype.inArray = function(val) {
    for(var i in this) {
        if (this[i] == val) return true;
    }
    return false;
}

function setCookie(name, value) {
    var expires = new Date();
    expires = expires.setTime(expires.getTime() + (1000 * 60 * 60 * 24 * 30));
    document.cookie = name + "=" + escape(value) + "; path=/" + ((expires == null) ? "" : "; expires=" + expires);
}

function getCookie(name) {
    var dc = document.cookie;
    var cname = name + "=";

    if (dc.length > 0) {
      begin = dc.indexOf(cname);
      if (begin != -1) {
        begin += cname.length;
        end = dc.indexOf(";", begin);
        if (end == -1) end = dc.length;
        return unescape(dc.substring(begin, end));
        }
      }
    return null;
}
function cleanCookie(name) {
    document.cookie = name + "=; expires=Thu, 01-Jan-70 00:00:01 GMT" + "; path=/";
}

var checker = {

    DBConnection: function() {
        ffDevTools.xhr.send('dev_tools/db/check.json', function(res) {
            code = '<span class="' + (res == "ok" ? 'c_g' : 'c_r') +'">';
            code += (res == "ok" ? "Database connection successfull" : res);
            code += '</span>';
            document.getElementById('check-connection-result').innerHTML = code;
        });

    }

}