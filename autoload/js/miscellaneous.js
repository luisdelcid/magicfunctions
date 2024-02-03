/**
 * @return int
 */
function __absint(maybeint = 0){
    return Math.abs(parseInt(maybeint));
}

/**
 * @return bool
 */
function __is_false(data){
    return (-1 < jQuery.inArray(String(data), ['0', 'false', 'off']));
}

/**
 * @return bool
 */
function __is_subclass_of(func = null, class_name = ''){
    if('function' !== typeof(func)){
        return false;
    }
    if(!class_name){
        return false;
    }
    while(func && func !== Function.prototype){
        if(func === window[class_name]){
            return true;
        }
        func = Object.getPrototypeOf(func);
    }
    return false;
}

/**
 * @return bool
 */
function __is_true(data){
    return (-1 < jQuery.inArray(String(data), ['1', 'on', 'true']));
}

/**
 * @return int
 */
function __rem_to_px(count){
    var unit = parseInt(jQuery('html').css('font-size'));
    if(!unit){
        unit = 16;
    }
    if(typeof count !== 'undefined' && count > 0){
        return (count * unit);
    } else {
        return unit;
    }
}

/**
 * @return void
 */
function __test(){
    console.log('Hello, World!');
}
