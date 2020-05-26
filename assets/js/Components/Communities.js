/* Component for LG/Communities */

export const modalLoader = '' +
    '<div class="d-flex justify-content-center">' +
    '  <div class="spinner-border m-4" role="status">' +
    '    <span class="sr-only">Loading...</span>' +
    '  </div>\n' +
    '</div>';

export const communitiesTable = (values, timestamp, name) => {
    let content = '' +
        '<table class="table table-sm table-borderless table-hover">' +
        '   <thead>' +
        '   <tr>' +
        '       <th scope="col" class="text-right">#</th>' +
        '       <th scope="col">' + name + '</th>' +
        '       <th scope="col">Label</th>' +
        '   </tr>' +
        '</thead>' +
        '<tbody>';

    let i = 0;
    values.forEach(function (community, index) {
        i++;

        content += '' +
            '<tr>' +
            '   <th scope="row" class="text-right text-nowrap">' + i + '</th>' +
            '   <td>' + community['raw'] + '</td>';

        if (community['name']) {
            content += '' +
                '<td><span class="bgh bgh-' + community['label'] + '">' + community['name'] + '</span></td>';

        } else {
            content += '' +
                '<td>&nbsp;</td>';
        }

        content += '</tr>';
    });

    content += '' +
        '</tbody>' +
        '</table>';

    if (typeof timestamp === 'number') {
        const date = new Date(timestamp * 1000);

        content += '' +
            '<div class="text-right">' +
            '   <span class="badge badge-info">Cached: ' + date.toLocaleString() + '</span>' +
            '</div>';
    }

    return content;
};
