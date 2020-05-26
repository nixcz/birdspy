/* Formatted row details */
import $ from 'jquery';

const rowLoader = '' +
    '<div class="d-flex justify-content-center">' +
    '  <div class="spinner-border m-4" role="status">' +
    '    <span class="sr-only">Loading...</span>' +
    '  </div>\n' +
    '</div>';

const formattedDetail = (blob, timestamp) => {
    let content = '' +
        '<pre><code>' + blob + '</code></pre>';

    if (typeof timestamp === 'number') {
        const date = new Date(timestamp * 1000);

        content += '' +
            '<div class="text-right">' +
            '   <span class="badge badge-info">Cached: ' + date.toLocaleString() + '</span>' +
            '</div>';
    }

    return content;
};

const rowDetail = (url, data) => {
    let output = $('<div class="container bg-light m-0 p-3"/>');

    output
        .append(rowLoader);

    $.ajax({
        url: url,
        data: data,
        dataType: 'json',
        success: function (json) {
            const rowDetail = formattedDetail(json.data.blob, json.timestamp);

            output
                .empty()
                .append(rowDetail);
        }
    });

    return output;
};

export const onClickDetailButton = (button, table, url, data) => {
    const tr = button.closest('tr');
    const row = table.row(tr);

    const buttonSpan = button.find('span.action-text');
    const buttonIcon = button.find('i.fas');
    const buttonSvg = button.find('svg.svg-inline--fa');

    if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');

        button.prop('title', 'Show Details');
        buttonSpan.text('Show');
        buttonIcon.toggleClass('fa-eye-slash fa-eye');
        buttonSvg.toggleClass('fa-eye-slash fa-eye');

    } else {

        const detail = rowDetail(url, data);

        row.child(detail).show();
        tr.addClass('shown');

        button.prop('title', 'Hide Details');
        buttonSpan.text('Hide');
        buttonIcon.toggleClass('fa-eye fa-eye-slash');
        buttonSvg.toggleClass('fa-eye fa-eye-slash');
    }
};
