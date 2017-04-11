// Get query variables
function getQuery() {
    var pairs = location.search.slice(1).split('&');

    var result = {};
    pairs.forEach((pair) => {
        pair = pair.split('=');
        result[pair[0]] = decodeURIComponent(pair[1] || '');
    });

    return JSON.parse(JSON.stringify(result));
}
var query = getQuery();

// Update form's `source` field
function updateSource() {
    var signature = [];
    // TODO: Add variant details, from `query`
    var keys = ['sd', 'st', 'si'];
    keys.forEach(function(key) {
        if (query[key] === undefined) {
            return;
        }

        signature.push(key + '=' + query[key]);
    });
    signature.sort();
    signature.unshift('vk');
    var source = signature.join('&');

    $('.ak-form [name=source]').val(source);
}

function saveAKID() {
    if (!query.akid && !query.referring_akid) {
        return;
    }

    try {
        var key = 'akid:' + location.pathname;
        localStorage[key] = query.akid || query.referring_akid;
    } catch (e) {
        // Most likely, cookies were disabled
    }
}

// Start
updateSource();
saveAKID();
