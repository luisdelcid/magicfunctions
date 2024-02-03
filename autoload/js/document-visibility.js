
/**
 * @return string
 */
function __document_visibility(){
    var hidden = '';
    if('undefined' !== typeof(document.hidden)){ // Opera 12.10 and Firefox 18 and later support
        hidden = 'hidden';
    } else if('undefined' !== typeof(document.webkitHidden)){
        hidden = 'webkitHidden';
    } else if('undefined' !== typeof(document.msHidden)){
        hidden = 'msHidden';
    } else if('undefined' !== typeof(document.mozHidden)){ // Deprecated
        hidden = 'mozHidden';
    }
    return (hidden ? document[hidden] : false);
}

/**
 * @return string
 */
function __document_visibility_change(event){
    var $this = event.data;
    __do_action('visibilitychange', __document_visibility()); // Hidden.
}

/**
 * @return string
 */
function __document_visibility_change_event(){
    var visibilityChange = '';
    if('undefined' !== typeof(document.hidden)){ // Opera 12.10 and Firefox 18 and later support
        visibilityChange = 'visibilitychange';
    } else if('undefined' !== typeof(document.webkitHidden)){
        visibilityChange = 'webkitvisibilitychange';
    } else if('undefined' !== typeof(document.msHidden)){
        visibilityChange = 'msvisibilitychange';
    } else if('undefined' !== typeof(document.mozHidden)){ // Deprecated
        visibilityChange = 'mozvisibilitychange';
    }
    return visibilityChange;
}

/**
 * @return void
 */
function __listen_for_document_visibility(){
    var $this = this;
    jQuery(function($){
        var event_name = __document_visibility_change_event(), events = jQuery._data(document, 'events');
        if('undefined' !== typeof(events[event_name])){
            jQuery.each(events[event_name], function(index, value){
                if('__document_visibility_change' === value.handler.name){
                    return;
                }
            });
        }
        jQuery(document).on(event_name, $this, __document_visibility_change);
    });
}
