function submitForm(event) {
    // Do not send the form
    event.preventDefault();

    // Read the form
    const html_form = event.target;
    const form = new FormData(html_form);

    // Drop empty fields
    for (const k of getEmptyValues(form)) {
        form.delete(k);
    }

    // Prepare the symbols entry
    if (form.has('symbols')) {
        const raw_symbols = form.get('symbols');
        const extracted_symbols = textBoxToDict(raw_symbols);
        for (const [k, v] of extracted_symbols) {
            form.append(`symbols[${k}]`, v);
        }
        form.delete('symbols');
    }

    // Charge the requested page
    if (html_form.method == 'get') {
        const data = new URLSearchParams(form);
        const url = html_form.action + '?' + data.toString();
        window.history.pushState(null, 'Searching...', url);
        document.location = url;
    } else {
        // This approach only works for 'GET' forms.
        // To handle 'POST' forms, a hidden html form must be
        // created and filled with the write elements.
    }
}


/**
 * Return keys with an empty associated value.
 * @param {FormData} form Form to extract empty values from.
 */
function getEmptyValues(form) {
    const empty = [];
    for (const [k, v] of form)
        if (v == '')
            empty.push(k);
    return empty;
}


/**
 * Parse a multi-line string and generates all couple `key: value` from it.
 * @param {String} symbols_text String to parse.
 * @param {String} line_sep Line separator, each lines have at most one couple `key: value`.
 * @param {String} data_sep Key/value separator.
 */
function* textBoxToDict(symbols_text, line_sep='\n', data_sep='=') {
    const arr = symbols_text.split(line_sep);

    for (const line of arr) {
        const [sym, ...rest] = line.split(data_sep);
        const key = sym.trim();
        if (key == '')
            continue
        const value = rest.join(data_sep).trim();
        yield [key, value];
    }
}

