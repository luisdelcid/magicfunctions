
/**
 * @return string
 */
function __add_query_arg(key, value, url){
    var a = {}, href = '';
    a = __get_a(url);
    if(a.protocol){
        href += a.protocol + '//';
    }
    if(a.hostname){
        href += a.hostname;
    }
    if(a.port){
        href += ':' + a.port;
    }
    if(a.pathname){
        if(a.pathname[0] !== '/'){
            href += '/';
        }
        href += a.pathname;
    }
    if(a.search){
        var search = [], search_object = __parse_str(a.search);
        jQuery.each(search_object, function(k, v){
            if(k != key){
                search.push(k + '=' + v);
            }
        });
        if(search.length > 0){
            href += '?' + search.join('&') + '&';
        } else {
            href += '?';
        }
    } else {
        href += '?';
    }
    href += key + '=' + value;
    if(a.hash){
        href += a.hash;
    }
    return href;
}

/**
 * @return string
 */
function __add_query_args(args, url){
    var a = {}, href = '';
    a = __get_a(url);
    if(a.protocol){
        href += a.protocol + '//';
    }
    if(a.hostname){
        href += a.hostname;
    }
    if(a.port){
        href += ':' + a.port;
    }
    if(a.pathname){
        if(a.pathname[0] !== '/'){
            href += '/';
        }
        href += a.pathname;
    }
    if(a.search){
        var search = [], search_object = __parse_str(a.search);
        jQuery.each(search_object, function(k, v){
            if(!(k in args)){
                search.push(k + '=' + v);
            }
        });
        if(search.length > 0){
            href += '?' + search.join('&') + '&';
        } else {
            href += '?';
        }
    } else {
        href += '?';
    }
    jQuery.each(args, function(k, v){
        href += k + '=' + v + '&';
    });
    href = href.slice(0, -1);
    if(a.hash){
        href += a.hash;
    }
    return href;
}

/**
 * @return object
 */
function __get_a(url){
    var a = document.createElement('a');
    if('undefined' !== typeof(url) && '' !== url){
        a.href = url;
    } else {
        a.href = jQuery(location).attr('href');
    }
    return a;
}

/**
 * @return string
 */
function __get_query_arg(key, url){
    var search_object = {};
    search_object = __get_query_args(url);
    if('undefined' !== typeof(search_object[key])){
        return search_object[key];
    }
    return '';
}

/**
 * @return object
 */
function __get_query_args(url){
    var a = {};
    a = __get_a(url);
    if(a.search){
        return __parse_str(a.search);
    }
    return {};
}

/**
 * @return string
 */
function __mu_plugins_url(){
    var l10n = __l10n();
    return 'undefined' !== typeof(l10n.mu_plugins_url) ? l10n.mu_plugins_url : '';
}

/**
 * @return object
 */
function __parse_str(str){
    var i = 0, search_object = {}, search_array = str.replace('?', '').split('&');
    for(i = 0; i < search_array.length; i ++){
        search_object[search_array[i].split('=')[0]] = search_array[i].split('=')[1];
    }
    return search_object;
}

/**
 * @return object|string
 */
function __parse_url(url, component){
    var a = {}, components = {}, keys = ['protocol', 'hostname', 'port', 'pathname', 'search', 'hash'];
    a = __get_a(url);
    if(typeof component === 'undefined' || component === ''){
        jQuery.map(keys, function(c){
            components[c] = a[c];
        });
        return components;
    } else if(jQuery.inArray(component, keys) !== -1){
        return a[component];
    } else {
        return '';
    }
}

/**
 * @return string
 */
function __plugins_url(){
    var l10n = __l10n();
    return 'undefined' !== typeof(l10n.plugins_url) ? l10n.plugins_url : '';
}

/**
 * @return string
 */
function __site_url(){
    var l10n = __l10n();
    return 'undefined' !== typeof(l10n.site_url) ? l10n.site_url : '';
}
