/*
 * Routes DataTable
 */

import $ from 'jquery';

import './datatables.js';
import { communitiesTable, modalLoader } from "./Components/Communities";
import { clearFilter, createButtons, resetSettings } from "./Components/DataTablesButtons";
import { appendCached } from "./Components/DataTablesHelpers";
import { routeDetailApi } from "./Components/Routing";
import { onClickDetailButton } from "./Components/RowDetail";

$(() => {

    const $table = $('#routes');
    const $tableModal = $('#route-table-modal');
    const $filterModal = $('#modal-filter-server-routes');
    const $filterForm = $('#filter-server-routes');

    const grouped = $table.data('grouped');
    const justInvalid = $table.data('just-invalid');
    const columnForTable = 0;

    const table = $table.DataTable({
        ajax: {
            url: $table.data('url'),
            timeout: 120000,
        },
        deferRender: true,
        createdRow: function (row, data, dataIndex) {
            if (data.highlighted) {
                $(row).addClass('table-warning');
            }
        },
        columns: [
            { // 0: Description
                visible: false,
                className: 'persistent',
                type: 'natural',
                data: 'description',
            },
            { // 1: Peer Name
                visible: false,
                className: 'persistent',
                type: 'natural',
                data: 'peer_name',
            },
            { // 2: Peer ASN
                visible: false,
                className: 'persistent',
                type: 'numeric',
                data: 'peer_asn',
            },
            { // 3: Table
                visible: false,
                className: 'persistent',
                type: 'natural-ci',
                data: 'table_name',
            },
            { // 4: Network
                className: 'export',
                type: 'ip-address',
                data: 'network',
                render: {
                    display: function (data, type, row) {
                        return '<a href="#" class="inline-filter" title="Filter by Network: ' + data + '" data-filter="' + data + '" data-filter-name="network">' + data + '</a>';
                    },
                }
            },
            { // 5: Neighbor Name
                visible: false,
                className: 'export persistent',
                type: 'natural',
                data: 'neighbor_name',
            },
            { // 6: Neighbor ASN
                visible: false,
                className: 'export persistent',
                type: 'numeric',
                data: 'neighbor_asn',
            },
            { // 7: Next Hop / IP Address
                className: 'export',
                type: 'ip-address',
                data: 'next_hop',
                render: {
                    display: function (data, type, row) {
                        let output = '';

                        output += '<a href="#" class="inline-filter" title="Filter by Next Hop: ' + data + '" data-filter="' + data + '" data-filter-name="next-hop">' + data + '</a>';
                        output += '<br>';
                        output += '<a href="#" class="inline-filter" title="Filter by Neighbor Name: ' + row.neighbor_name + '" data-filter="' + row.neighbor_name + '" data-filter-name="neighbor-name">(' + row.neighbor_name + ')</a>';

                        return output;
                    },
                    filter: function (data, type, row) {
                        return data + ' ' + row.neighbor_name;
                    },
                }
            },
            { // 8: Flags
                className: 'export',
                data: 'primary',
                render: {
                    display: function (data, type, row) {
                        let output = '';

                        row.flags.forEach(function (item) {
                            output += '<a href="#" class="inline-filter bgh bgh-' + item.label + ' text-nowrap mx-1" role="contentinfo" title="' + item.title + '" data-filter="(' + item.id + ')" data-filter-name="route-flag" data-toggle="tooltip">' + item.name + '</a>';
                        });

                        return output;
                    },
                    filter: function (data, type, row) {
                        let values = [];

                        row.flags.forEach(function (item) {
                            values.push('(' + item.id + ')');
                        });

                        return values.join(' ');
                    },
                    export: function (data, type, row) {
                        let values = [];

                        row.flags.forEach(function (item) {
                            values.push(item.title);
                        });

                        return values.join(', ');
                    },
                }
            },
            { // 9: Metric
                visible: false,
                className: 'export text-right text-nowrap',
                type: 'numeric',
                data: 'metric',
            },
            { // 10: Communities
                visible: false,
                orderable: false,
                className: 'export persistent',
                data: 'communities',
                render: {
                    _: function (data, type, row) {
                        return formatCommunities(data.values);
                    },
                    filter: function (data, type, row) {
                        return formatCommunitiesForFilter(data.filter_values);
                    }
                },
            },
            { // 11: Communities Count
                className: 'export text-right text-nowrap',
                type: 'numeric',
                data: 'communities',
                render: {
                    _: 'count',
                    display: function (data, type, row) {
                        if (data.count > 0) {
                            return '<span tabindex="0" class="bgh bgh-pill bgh-primary" role="button" title="Show Communities" data-toggle="modal" data-key="communities" data-title="Communities: ' + data.count + '" data-target="#route-table-modal">' + data.count + '</span>';
                        }

                        return '<span class="bgh bgh-pill bgh-secondary disabled" title="Communities">0</span>';

                    }
                }
            },
            { // 12: Large Communities
                visible: false,
                orderable: false,
                className: 'export persistent',
                data: 'large_communities',
                render: {
                    _: function (data, type, row) {
                        return formatCommunities(data.values);
                    },
                    filter: function (data, type, row) {
                        return formatCommunitiesForFilter(data.filter_values);
                    }
                },
            },
            { // 13: Large Communities Count
                className: 'export text-right text-nowrap',
                type: 'numeric',
                data: 'large_communities',
                render: {
                    _: 'count',
                    display: function (data, type, row) {
                        if (data.count > 0) {
                            return '<span tabindex="0" class="bgh bgh-pill bgh-primary" role="button" title="Show Large Communities" data-toggle="modal" data-key="large_communities" data-title="Large Communities: ' + data.count + '" data-target="#route-table-modal">' + data.count + '</span>';
                        }

                        return '<span class="bgh bgh-pill bgh-secondary disabled" title="Large Communities">0</span>';
                    }
                }
            },
            { // 14: Extended Communities
                visible: false,
                orderable: false,
                className: 'export persistent',
                data: 'extended_communities',
                render: {
                    _: function (data, type, row) {
                        return formatCommunities(data.values);
                    },
                    filter: function (data, type, row) {
                        return formatCommunitiesForFilter(data.filter_values);
                    }
                },
            },
            { // 15: Extended Communities Count
                className: 'export text-right text-nowrap',
                type: 'numeric',
                data: 'extended_communities',
                render: {
                    _: 'count',
                    display: function (data, type, row) {
                        if (data.count > 0) {
                            return '<span tabindex="0" class="bgh bgh-pill bgh-primary" role="button" title="Show Extended Communities" data-toggle="modal" data-key="extended_communities" data-title="Extended Communities: ' + data.count + '" data-target="#route-table-modal">' + data.count + '</span>';
                        }

                        return '<span class="bgh bgh-pill bgh-secondary disabled" title="Extended Communities">0</span>';
                    }
                }
            },
            { // 16: AS Path
                orderable: false,
                className: 'export',
                data: 'as_path',
                render: function (data, type, row) {
                    let content = '';

                    data.forEach(function (item) {
                        content += '<a href="#" class="inline-filter" title="Filter by AS in Path: ' + item + '" data-filter="' + item + '" data-filter-name="as-path">' + item + '</a> ';
                    });

                    return content;
                }
            },
            { // 17: Action
                orderable: false,
                className: 'persistent',
                data: 'background',
                render: {
                    display: function (data, type, row) {
                        let output = '' +
                            '<div class="btn-group">' +
                            '  <button type="button" class="btn btn-' + data + ' btn-sm text-nowrap details-control" title="Show Details"><i class="fas fa-eye fa-fw"></i> <span class="action-text">Show</span></button>' +
                            '  <button type="button" class="btn btn-' + data + ' btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                            '    <span class="sr-only">Toggle Dropdown</span>' +
                            '  </button>' +
                            '  <div class="dropdown-menu dropdown-menu-right">';

                        if (row.communities.count > 0) {
                            output += '' +
                                '    <a class="dropdown-item" href="#" role="button" title="Show Communities" data-toggle="modal" data-key="communities" data-title="Communities: ' + row.communities.count + '" data-target="#route-table-modal"><i class="fas fa-eye fa-fw"></i> Show Communities</a>';
                        }

                        if (row.large_communities.count > 0) {
                            output += '' +
                                '    <a class="dropdown-item" href="#" role="button" title="Show Large Communities" data-toggle="modal" data-key="large_communities" data-title="Large Communities: ' + row.large_communities.count + '" data-target="#route-table-modal"><i class="fas fa-eye fa-fw"></i> Show Large Communities</a>';
                        }

                        if (row.extended_communities.count > 0) {
                            output += '' +
                                '    <a class="dropdown-item" href="#" role="button" title="Show Extended Communities" data-toggle="modal" data-key="extended_communities" data-title="Extended Communities: ' + row.extended_communities.count + '" data-target="#route-table-modal"><i class="fas fa-eye fa-fw"></i> Show Extended Communities</a>';
                        }

                        output += '' +
                            '  </div>' +
                            '</div>';

                        return output;
                    }
                }
            }
        ],
        order: [
            [3, 'asc'],  // Table
            [4, 'asc'],  // Network
            [8, 'desc'], // Flags (Primary)
            [7, 'asc']   // Next Hop
        ],
        initComplete: function (settings, json) {
            if (justInvalid) {
                const invalidFlag = '(invalid)';

                $('select[name="route-flag"]', $filterForm).val(invalidFlag).change();

                this.api().column(8).search(invalidFlag);
                this.api().draw();
            }

            appendCached(json);
        },
        drawCallback: function (settings) {
            if (grouped) {
                const api = this.api();
                const rows = api.rows({ page: 'current' }).nodes();
                let last = null;

                api.column(columnForTable, { page: 'current' }).data().each(function (group, i) {
                    if (last !== group) {
                        $(rows).eq(i).before(
                            '<tr class="group table-primary"><th scope="col" colspan="17">' + group + '</th></tr>'
                        );

                        last = group;
                    }
                });
            }
        }
    });

    createButtons(table);

    table.on('draw', function () {
        $('[data-toggle="tooltip"]').tooltip({
            container: 'body',
            html: true
        });
    });

    // Add event listener for opening and closing details
    table.on('click', 'button.details-control', function (e) {
        e.preventDefault();

        const button = $(this);
        const tr = button.closest('tr');
        const row = table.row(tr);

        const url = routeDetailApi();
        const data = {
            table_id: row.data().table_id,
            route_id: row.data().id,
        };

        onClickDetailButton(button, table, url, data);
    });

    // Order by the grouped row
    $('#routes tbody').on('click', 'tr.group', function () {
        const currentOrder = table.order()[0];

        if (currentOrder[0] === columnForTable && currentOrder[1] === 'asc') {
            table.order([columnForTable, 'desc']).draw();
        } else {
            table.order([columnForTable, 'asc']).draw();
        }
    });

    $tableModal.on('show.bs.modal', function (e) {
        const button = $(e.relatedTarget);
        const tr = button.closest('tr');
        const row = table.row(tr);
        const rowData = row.data();
        const timestamp = table.ajax.json().timestamp;

        const modal = $(this);

        modal.find('.modal-title').text(button.data('title'));
        modal.find('.modal-body').append(modalLoader);

        let values = [];
        let columnName;
        let content = '';

        if (button.data('key') === 'communities') {
            values = rowData.communities.values;
            columnName = 'Community';
        }

        if (button.data('key') === 'large_communities') {
            values = rowData.large_communities.values;
            columnName = 'Large Community';
        }

        if (button.data('key') === 'extended_communities') {
            values = rowData.extended_communities.values;
            columnName = 'Extended Community';
        }

        content = communitiesTable(values, timestamp, columnName);

        modal.find('.modal-body').empty().append(content);
    });

    $tableModal.on('hidden.bs.modal', function () {
        const modal = $(this);

        modal.find('.modal-title').empty();
        modal.find('.modal-body').empty();
    });

    // Filter
    $filterForm.on('submit', function (e) {
        e.preventDefault();

        table.column(1).search($('input[name="peer-name"]').val());
        table.column(2).search($('input[name="peer-asn"]').val());
        table.column(5).search($('input[name="neighbor-name"]').val());
        table.column(6).search($('input[name="neighbor-asn"]').val());
        table.column(4).search($('input[name="network"]').val());
        table.column(9).search($('input[name="metric"]').val());
        table.column(7).search($('input[name="next-hop"]').val());
        table.column(16).search($('input[name="as-path"]').val());
        table.column(0).search($('input[name="description"]').val());
        table.column(3).search($('input[name="table"]').val());

        table.column(8).search($('select[name="route-flag"]').val().join(' '));

        let values = [];
        values[0] = [];
        values[1] = [];
        values[2] = [];

        $('select[name="route-communities"]').val()
            .map(item => {
                const splitted = item.split(/_/);

                if (splitted[0] === 'c') {
                    values[0].push(splitted[1]);
                }

                if (splitted[0] === 'lgc') {
                    values[1].push(splitted[1]);
                }

                if (splitted[0] === 'ec') {
                    values[2].push(splitted[1]);
                }
            });

        table.column(10).search(values[0].join(' '));
        table.column(12).search(values[1].join(' '));
        table.column(14).search(values[2].join(' '));

        $filterModal.modal('hide');

        table.draw();
    });

    $('button.clear', $filterForm).on('click', function (e) {
        e.preventDefault();

        $filterModal.modal('hide');

        clearFilter(table);
    });

    $('button.reset', $filterForm).on('click', function (e) {
        e.preventDefault();

        resetSettings(table);
    });

    // Add event listener for filtering
    table.on('click', 'a.inline-filter, button.inline-filter', function (e) {
        e.preventDefault();

        const button = $(this);

        const visIdx = button.parent().index();
        const index = table.column.index('fromVisible', visIdx);
        const value = button.data('filter');
        const name = button.data('filter-name');

        // Fill Filter Input
        $('input[name="' + name + '"]', $filterForm).val(value).change();

        // Fill Select
        const $select = $('select[name="' + name + '"]', $filterForm);

        if ($select.length) {
            const values = $select.data('selectpicker').val();

            values.push(value);

            $select.val(values).change();

            table.column(index).search(values.join(' ')).draw();

        } else {
            // Search
            table.column(index).search(value).draw();
        }
    });

    /*
    table.on('column-visibility.dt', function (e, settings, column, state) {
        const th = table.column(column).header();

        if (state) {
            $(th).toggleClass('invisible visible');

        } else {
            $(th).toggleClass('visible invisible');
        }
    });
    */

    /*
    $('.selectpicker')
        .on('show.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            //$('.modal.show').data('bs.modal').options.keyboard = false;
        })
        .on('hide.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            //$('.modal.show').data('bs.modal').options.keyboard = true;
        });
    */

    function formatCommunities(values) {
        const communities = values.map(item => {
            return item['raw'] + (null === item['name'] ? '' : ' ' + item['name']);
        });

        return communities.join(', ');
    }

    function formatCommunitiesForFilter(values) {
        const communities = values.map(item => {
            return '(' + item + ')';
        });

        return communities.join(' ');
    }

});
