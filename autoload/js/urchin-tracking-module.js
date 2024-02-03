/**
 * @return array
 */
function __current_utm(){
    var object_name = __str_prefix('utm');
    return ('undefined' !== typeof(window[object_name]) ? window[object_name] : {});
}
