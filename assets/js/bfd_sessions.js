/*
 * BFD Sessions Summary DataTable
 */

import $ from 'jquery';

import './datatables.js';
import { tableRoutes } from "./Components/Routing";
import { clearFilter, createButtons, resetSettings } from "./Components/DataTablesButtons";

$(() => {

    const $table = $('#bfd-sessions');
    const $filterModal = $('#modal-filter-bfd-sessions');
    const $filterForm = $('#filter-bfd-sessions');

    const serverId = $table.data('server');

    const table = $table.DataTable({
        ajax: $table.data('url'),
        createdRow: function (row, data, dataIndex) {
            if (data.state !== 'Up') {
                $(row).addClass('table-warning');
            }
        },
        columns: [
            { // 0: Peer Name
                className: 'export persistent',
                type: 'natural',
                data: 'peer_name',
            },
            { // 1: Table
                className: 'export',
                type: 'natural',
                data: 'table',
                render: {
                    display: function (data, type, row) {
                        return '<a href="' + tableRoutes(serverId, data) + '" title="Routes from table: ' + data + '">' + data + '</a>';
                    },
                }
            },
            { // 2: IP Address
                className: 'export text-nowrap',
                type: 'ip-address',
                data: 'ip_address',
            },
            { // 3: Description
                visible: false,
                className: 'export',
                type: 'natural',
                data: 'description',
            },
            { // 4: ASN
                // visible: false,
                className: 'export text-right text-nowrap',
                type: 'numeric',
                data: 'asn',
                render: {
                    display: function (data, type, row) {
                        return '<a href="#" class="inline-filter" title="Filter by ASN: ' + data + '" data-filter="' + data + '" data-filter-name="asn">' + data + '</a>';
                    }
                }
            },
            { // 5: Interface
                className: 'export',
                type: 'natural',
                data: 'interface',
                render: {
                    display: function (data, type, row) {
                        return '<a href="#" class="inline-filter" title="Filter by Interface: ' + data + '" data-filter="' + data + '" data-filter-name="interface">' + data + '</a>';
                    }
                }
            },
            { // 6: State
                className: 'export',
                data: 'state',
                render: {
                    display: function (data, type, row) {
                        let bg;

                        switch (data) {
                            case 'Up':
                                bg = 'bgh-success';
                                break;
                            default:
                                bg = 'bgh-warning';
                        }

                        return '<a href="#" class="inline-filter bgh ' + bg + '" title="Filter by State: ' + data + '" data-filter="' + data + '" data-filter-name="bfd-state">' + data + '</a>';
                    }
                }
            },
            { // 7: Since
                className: 'export text-right',
                type: 'numeric',
                data: 'since',
                render: {
                    _: 'timestamp',
                    display: function (data, type, row) {
                        if (typeof data.timestamp !== 'number') {
                            return 'n/a';
                        }

                        let date = new Date(data.timestamp * 1000);

                        return date.toLocaleString();
                    },
                    filter: function (data, type, row) {
                        if (typeof data.timestamp !== 'number') {
                            return null;
                        }

                        let date = new Date(data.timestamp * 1000);

                        return date.toLocaleString() + ' ' + data.value;
                    },
                    export: function (data, type, row) {
                        return data.value;
                    },
                }
            },
            { // 8: Interval
                className: 'export text-right text-nowrap',
                type: 'numeric',
                data: 'interval',
                render: {
                    display: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return 'n/a';
                        }

                        return data.toLocaleString(undefined, { minimumFractionDigits: 3 });
                    },
                    export: function (data, type, row) {
                        return data;
                    },
                }
            },
            { // 9: Timeout
                className: 'export text-right text-nowrap',
                type: 'numeric',
                data: 'timeout',
                render: {
                    display: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return 'n/a';
                        }

                        return data.toLocaleString(undefined, { minimumFractionDigits: 3 });
                    },
                    export: function (data, type, row) {
                        return data;
                    },
                }
            },
            { // 10: Action
                className: 'persistent',
                data: null,
                orderable: false,
                render: {
                    display: function (data, type, row) {
                        let buttonClass;

                        switch (row.state) {
                            case 'Up':
                                buttonClass = 'btn-success';
                                break;
                            case 'Down':
                                buttonClass = 'btn-warning';
                                break;
                            case 'Init':
                                buttonClass = 'btn-warning';
                                break;
                            default:
                                buttonClass = 'btn-dark';
                        }

                        return '<a href="' + tableRoutes(serverId, row.table) + '" class="btn ' + buttonClass + ' btn-sm text-nowrap" title="Table Routes" role="button"><i class="fas fa-layer-group fa-fw"></i> Table</a>';
                    }
                }
            }
        ],
        order: [
            [7, 'desc'],
            [0, 'asc']
        ]
    });

    createButtons(table);

    table.on('draw', function () {
        $('[data-toggle="tooltip"]').tooltip({
            container: 'body',
            html: true
        });
    });

    // Filter
    $filterForm.on('submit', function (e) {
        e.preventDefault();

        //console.log($(this).serializeArray());

        table.column(0).search($('input[name="peer-name"]').val());
        table.column(1).search($('input[name="table"]').val());
        table.column(2).search($('input[name="ip-address"]').val());
        table.column(3).search($('input[name="description"]').val());
        table.column(4).search($('input[name="asn"]').val());
        table.column(5).search($('input[name="interface"]').val());
        table.column(8).search($('input[name="interval"]').val());
        table.column(9).search($('input[name="timeout"]').val());

        const bfdState = $('select[name="bfd-state"]').val()
            .map(item => {
                return $.fn.dataTable.util.escapeRegex(item);
            })
            .join('|');

        table.column(6).search(bfdState ? bfdState : '', true, false);

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

        // Filled Filter Input
        $('input[name="' + name + '"],select[name="' + name + '"]', $filterForm).val(value).change();

        // Search
        table.column(index).search(value).draw();
    });

});
