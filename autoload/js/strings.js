/**
 * @return string
 */
function __canonicalize(str = ''){
    str = __sanitize_title(str);
    str = str.replaceAll('-', '_');
    return str;
}

/**
 * https://github.com/locutusjs/locutus/blob/master/src/php/strings/ltrim.js
 *
 * @return string
 */
function __ltrim(str, charlist){
    charlist = !charlist ? ' \\s\u00A0' : (charlist + '').replace(/([[\]().?/*{}+$^:])/g, '$1');
    const re = new RegExp('^[' + charlist + ']+', 'g');
    return (str + '').replace(re, '');
}

/**
 * TODO: Improve.
 *
 * @return string
 */
function __remove_accents(str = ''){
    str = str.replace(new RegExp('[àáâãäå]', 'g'), 'a');
    str = str.replace(new RegExp('[èéêë]', 'g'), 'e');
    str = str.replace(new RegExp('[ìíîï]', 'g'), 'i');
    str = str.replace(new RegExp('[òóôõö]', 'g'), 'o');
    str = str.replace(new RegExp('[ùúûü]', 'g'), 'u');
    return str;
}

/**
 * https://github.com/locutusjs/locutus/blob/master/src/php/strings/rtrim.js
 *
 * @return string
 */
function __rtrim(str, charlist){
    charlist = !charlist ? ' \\s\u00A0' : (charlist + '').replace(/([[\]().?/*{}+$^:])/g, '\\$1');
    const re = new RegExp('[' + charlist + ']+$', 'g');
    return (str + '').replace(re, '');
}

/**
 * @return string
 */
function __sanitize_title(str = ''){
    str = __remove_accents(str);
    str = __sanitize_title_with_dashes(str);
    return str;
}

/**
 * TODO: Improve.
 *
 * @return string
 */
function __sanitize_title_with_dashes(str = ''){
    str = str.toLowerCase();
    str = str.replace(/\s+/g, ' ');
    str = str.trim();
    str = str.replaceAll(' ', '-');
    str = str.replace(/[^a-z0-9-_]/g, '');
    return str;
}

/**
 * @return string
 */
function __str_prefix(str = '', prefix = ''){
    prefix = prefix.replaceAll('\\', '_'); // Fix namespaces.
    prefix = __canonicalize(prefix);
    prefix = __rtrim(prefix, '_');
    if(!prefix){
        prefix = __prefix();
    }
    str = __remove_whitespaces(str);
    if(!str){
        return prefix;
    }
    if(0 === str.indexOf(prefix)){
        return str; // Text is already prefixed.
    }
    return prefix + '_' + str;
}

/**
 * @return string
 */
function __str_slug(str = '', slug = ''){
    slug = slug.replaceAll('_', '-'); // Fix canonicalized.
    slug = slug.replaceAll('\\', '-'); // Fix namespaces.
    slug = __sanitize_title(slug);
    slug = __rtrim(slug, '-');
    if(!slug){
        slug = __slug();
    }
    str = __remove_whitespaces(str);
    if(!slug){
        return slug;
    }
    if(0 === str.indexOf(slug)){
        return str; // Text is already slugged.
    }
    return slug + '-' + str;
}
