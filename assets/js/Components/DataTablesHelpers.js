/* DataTables Helpers */

import $ from "jquery";

export const appendCached = (json) => {
    if (typeof json.timestamp !== 'number') {
        return;
    }

    const timestamp = json.timestamp;

    if (timestamp) {
        const date = new Date(timestamp * 1000);

        const cachedSpan = '' +
            '<div>' +
            '   <span class="badge badge-info">Cached: ' + date.toLocaleString() + '</span>' +
            '</div>';

        $('div.dataTables_wrapper').append(cachedSpan);
    }
};
