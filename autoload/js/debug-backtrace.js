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
        var result = (/(http[s]?:\/\/.*):(\d+):(\d+)/g).exec(value); // Array or null.
        if(result && 3 < result.length){
            var function_name = value.replace('(' + result[0] + ')', '').trim().replace('at ', '');
            var class_name = function_name.split(".");
            if(1 < class_name.length){
                function_name = class_name[1];
                class_name = class_name[0];
            } else {
                function_name = class_name[0];
                class_name = '';
            }
            errors.push({
                class: class_name,
                file: result[1].replace(__site_url() + '/', ''),
                function: function_name,
                index: result[3],
        		line: result[2],
                url: result[1],
            });
        }
    });
    return errors;
}
