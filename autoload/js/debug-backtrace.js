/**
 * @return mixed
 */
function __backtrace(element = '', index = 0){
    var caller = {};
    var debug = [];
    var fake_error = null;
    var fake_function = null;
	var index = __absint(index) + 1;
	var limit = index + 1;
    var pairs = {
        class: '',
        file: '',
        function: '',
		line: 0,
        url: '',
    };
    try {
        fake_function();
    } catch(error){
        fake_error = error;
    }
    if(!fake_error instanceof Error){
        return pairs;
    }
    debug = __error_backtrace(fake_error);
    if(limit > debug.length){
        return pairs;
    }
    caller = debug[index];
	if(!element){
		return caller;
	}
	if('undefined' !== typeof(caller[element])){
		return caller[element];
	}
	return false;
}

/**
 * @return string
 */
function __error_backtrace(error = null){
    var errors = [];
    var pairs = {
        class: '',
        file: '',
        function: '',
		line: 0,
        url: '',
    };
    var stack = null;
    if('undefined' !== typeof(error.stack)){
        stack = error.stack;
    } else if('undefined' !== typeof(error.prototype.stack)){
        stack = error.prototype.stack;
    } else {
        return errors;
    }
    var values = stack.split("\n");
    jQuery.each(values, function(index, value){
        var result = (/(http[s]?:\/\/.*):\d+:\d+/g).exec(value); // Array or null.
        if(result && 1 < result.length){
            var fn = value.replace('(' + result[0] + ')', '').trim().replace('at ', '');
            errors.push({
                class: '',
                file: '',
                function: fn,
        		line: 0,
                url: result[1],
            });
        }
    });
    return errors;
}
