/*
 * BGP Protocols Summary DataTable
 */

import $ from 'jquery';

import './datatables.js';
import {
    bgpProtocolDetailApi,
    importedRoutes,
    exportedRoutes,
    tableRoutes,
    tableInvalidRoutes,
    tableFilteredRoutes,
    communityLookup
} from './Components/Routing';
import { onClickDetailButton } from "./Components/RowDetail";
import { clearFilter, createButtons, resetSettings } from "./Components/DataTablesButtons";

$(() => {

    const $table = $('#bgp-protocols');
    const $filterModal = $('#modal-filter-bgp-protocols');
    const $filterForm = $('#filter-bgp-protocols');

    const serverId = $table.data('server');

    const table = $table.DataTable({
        ajax: $table.data('url'),
        deferRender: true,
        createdRow: function (row, data, dataIndex) {
            if (data.highlighted) {
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
                    }
                }
            },
            { // 2: IP Address
                className: 'export',
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
            { // 5: BGP State
                className: 'export',
                data: 'bgp_state.value',
                render: {
                    display: function (data, type, row) {
                        const state = row.bgp_state;

                        let bg;
                        switch (state['value']) {
                            case 'Established':
                                bg = 'bgh-success';
                                break;
                            default:
                                bg = 'bgh-warning';
                        }

                        return '<a href="#" class="inline-filter bgh ' + bg + '" role="contentinfo" title="' + data + '" data-filter="' + data + '" data-filter-name="bgp-state" data-toggle="tooltip">' + state['shortcut'] + '</a>';
                    }
                }
            },
            { // 6: PfxRatio
                // visible: false,
                className: 'export',
                type: 'numeric',
                data: 'import_ratio',
                render: {
                    display: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return '' +
                                '<div class="progress" style="height: 2em; width: 80px">' +
                                '  <div class="progress-bar bg-secondary" role="progressbar" style="width: 100%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">n/a</div>' +
                                '</div>';
                        }

                        let bg = '';

                        if (data >= .9) {
                            bg = 'bg-danger';

                        } else if (data >= .8) {
                            bg = 'bg-warning';

                        } else if (data >= .7) {
                            bg = 'bg-info';

                        } else {
                            bg = 'bg-success';
                        }

                        let pct = data * 100;

                        return '' +
                            '<div class="progress" title="Imported: ' + Math.round(pct) + '%" role="contentinfo" style="height: 2em; width: 80px" data-toggle="tooltip">' +
                            '  <div class="progress-bar ' + bg + '" role="progressbar" style="width: ' + pct + '%;" aria-valuenow="' + pct + '" aria-valuemin="0" aria-valuemax="100">' + Math.round(pct) + '%</div>' +
                            '</div>';

                    },
                    export: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return null;
                        }

                        let pct = data * 100;

                        return Math.round(pct) + '%';
                    },
                    sort: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return -1;
                        }

                        return data;
                    }
                }
            },
            { // 7: InvRatio
                // visible: false,
                className: 'export',
                type: 'numeric',
                data: 'invalid_ratio',
                render: {
                    display: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return '' +
                                '<div class="progress" style="height: 2em; width: 80px">' +
                                '  <div class="progress-bar bg-secondary" role="progressbar" style="width: 100%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">n/a</div>' +
                                '</div>';
                        }

                        if (data > 1) {
                            return '' +
                                '<div class="progress" title="Out of Sync" role="contentinfo" style="height: 2em; width: 80px" data-toggle="tooltip">' +
                                '  <div class="progress-bar bg-warning" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">n/a</div>' +
                                '</div>';
                        }

                        const i = data * 100;
                        const v = 100 - i;

                        const invalid = Math.round(i);
                        const valid = 100 - invalid;

                        let showValid = '';
                        let showInvalid = '';

                        if (valid >= 50) {
                            showValid = valid + '%';
                        } else {
                            showInvalid = invalid + '%';
                        }

                        return '' +
                            '<div class="progress" title="Valid: ' + valid + '%, Invalid: ' + invalid + '%" role="contentinfo" style="height: 2em; width: 80px" data-toggle="tooltip">' +
                            '  <div class="progress-bar bg-success" role="progressbar" style="width: ' + v + '%;" aria-valuenow="' + v + '" aria-valuemin="0" aria-valuemax="100">' + showValid + '</div>' +
                            '  <div class="progress-bar bg-danger" role="progressbar" style="width: ' + i + '%;" aria-valuenow="' + i + '" aria-valuemin="0" aria-valuemax="100">' + showInvalid + '</div>' +
                            '</div>';

                    },
                    export: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return null;
                        }

                        if (data > 1) {
                            return 100 + '%';
                        }

                        let pct = data * 100;

                        return Math.round(pct) + '%';
                    },
                    sort: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return -1;
                        }

                        return data;
                    }
                }
            },
            { // 8: PfxLimit
                visible: false,
                className: 'export text-right text-nowrap',
                type: 'numeric',
                data: 'import_limit',
                render: {
                    display: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return 'n/a';
                        }

                        return data.toLocaleString();
                    }
                }
            },
            { // 9: PfxRcd
                className: 'export persistent text-right text-nowrap',
                type: 'numeric',
                data: 'imported_routes',
                render: {
                    display: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return '<span class="bgh bgh-secondary bgh-pill">n/a</span>';
                        }

                        if (data > 0) {
                            return '<a href="' + importedRoutes(serverId, row.protocol) + '" class="bgh bgh-primary bgh-pill" role="button" title="Routes imported from protocol: ' + row.protocol + '">' + data.toLocaleString() + '</a>';
                        }

                        if (data === 0) {
                            return '<span class="bgh bgh-secondary bgh-pill">0</span>';
                        }
                    },
                    export: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return null;
                        }

                        return data;
                    },
                    sort: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return -1;
                        }

                        return data;
                    }
                }
            },
            { // 10: PfxExp
                className: 'export persistent text-right text-nowrap',
                type: 'numeric',
                data: 'exported_routes',
                render: {
                    display: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return '<span class="bgh bgh-secondary bgh-pill">n/a</span>';
                        }

                        if (data > 0) {
                            return '<a href="' + exportedRoutes(serverId, row.protocol) + '" class="bgh bgh-primary bgh-pill" role="button" title="Routes exported to protocol: ' + row.protocol + '">' + data.toLocaleString() + '</a>';
                        }

                        if (data === 0) {
                            return '<span class="bgh bgh-secondary bgh-pill">0</span>';
                        }
                    },
                    export: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return null;
                        }

                        return data;
                    },
                    sort: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return -1;
                        }

                        return data;
                    }
                },
            },
            { // 11: PfxInv
                className: 'export persistent text-right text-nowrap',
                data: 'invalid_routes',
                render: {
                    display: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return '<span class="bgh bgh-secondary bgh-pill">n/a</span>';
                        }

                        if (data > 0) {
                            return '<a href="' + tableInvalidRoutes(serverId, row.table) + '" class="bgh bgh-red bgh-pill" role="button" title="Invalid routes from table: ' + row.table + '">' + data.toLocaleString() + '</a>';
                        }

                        if (data === 0) {
                            return '<span class="bgh bgh-secondary bgh-pill">0</span>';
                        }
                    },
                    export: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return null;
                        }

                        return data;
                    },
                    sort: function (data, type, row) {
                        if (typeof data !== 'number') {
                            return -1;
                        }

                        return data;
                    }
                },
            },
            { // 12: Status
                visible: false,
                className: 'export',
                data: 'state',
                render: {
                    display: function (data, type, row) {
                        let bg;
                        let text = data;

                        switch (data) {
                            case 'up':
                                bg = 'bgh-success';
                                text = 'Up';
                                break;
                            case 'start':
                                bg = 'bgh-warning';
                                text = 'Start';
                                break;
                            default:
                                bg = 'bgh-secondary';
                        }

                        return '<a href="#" class="inline-filter bgh ' + bg + '" role="contentinfo" data-filter="' + data + '" data-filter-name="status">' + text + '</a>';
                    }
                }
            },
            { // 13: State Changed
                className: 'export text-right',
                type: 'numeric',
                data: 'state_changed',
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
                    }
                }
            },
            { // 14: Action
                className: 'persistent',
                data: null,
                orderable: false,
                render: {
                    display: function (data, type, row) {
                        let buttonClass;

                        switch (row.state) {
                            case 'up':
                                buttonClass = 'btn-success';
                                break;
                            case 'start':
                                buttonClass = 'btn-warning';
                                break;
                            default:
                                buttonClass = 'btn-outline-dark';
                        }

                        let output = '' +
                            '<div class="btn-group">' +
                            '  <button type="button" class="btn ' + buttonClass + ' btn-sm text-nowrap details-control" title="Show Details"><i class="fas fa-eye fa-fw"></i> <span class="action-text">Show</span></button>' +
                            '  <button type="button" class="btn ' + buttonClass + ' btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                            '    <span class="sr-only">Toggle Dropdown</span>' +
                            '  </button>' +
                            '  <div class="dropdown-menu dropdown-menu-right">';

                        output += '' +
                            '    <a href="' + tableRoutes(serverId, row.table) + '" class="dropdown-item"><i class="fas fa-layer-group fa-fw"></i> Table Routes</a>';

                        if (row.imported_routes > 0) {
                            output += '' +
                                '    <a href="' + importedRoutes(serverId, row.protocol) + '" class="dropdown-item"><i class="fas fa-arrow-circle-right fa-fw"></i> Imported Routes</a>';
                        }

                        if (row.exported_routes > 0) {
                            output += '' +
                                '    <a href="' + exportedRoutes(serverId, row.protocol) + '" class="dropdown-item"><i class="fas fa-arrow-circle-left fa-fw"></i> Exported Routes</a>';
                        }

                        if (row.selected_routes > 0) {
                            output += '' +
                                '    <a href="' + tableFilteredRoutes(serverId, row.table) + '" class="dropdown-item"><i class="fas fa-question-circle fa-fw"></i> Filtered Routes</a>';
                        }

                        if (row.invalid_routes > 0) {
                            output += '' +
                                '    <a href="' + tableInvalidRoutes(serverId, row.table) + '" class="dropdown-item"><i class="fas fa-exclamation-circle fa-fw"></i> Invalid Routes</a>';
                        }

                        output += '' +
                            '<div class="dropdown-divider"></div>';

                        output += '' +
                            '    <a href="' + communityLookup(serverId, row.table) + '" class="dropdown-item"><i class="fas fa-search fa-fw"></i> Community Lookup</a>';

                        output += '' +
                            '  </div>' +
                            '</div>';

                        return output;
                    }
                }
            }
        ],
        order: [
            [13, 'desc'],
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

    // Add event listener for opening and closing details
    table.on('click', 'button.details-control', function (e) {
        e.preventDefault();

        const button = $(this);
        const tr = button.closest('tr');
        const row = table.row(tr);

        const url = bgpProtocolDetailApi();
        const data = {
            protocol_id: row.data().id,
        };

        onClickDetailButton(button, table, url, data);
    });

    // Filter
    $filterForm.on('submit', function (e) {
        e.preventDefault();

        table.column(0).search($('input[name="peer-name"]').val());
        table.column(1).search($('input[name="table"]').val());
        table.column(2).search($('input[name="ip-address"]').val());
        table.column(3).search($('input[name="description"]').val());
        table.column(4).search($('input[name="asn"]').val());

        const bgpState = $('select[name="bgp-state"]').val()
            .map(item => {
                return $.fn.dataTable.util.escapeRegex(item);
            })
            .join('|');

        table.column(5).search(bgpState ? bgpState : '', true, false);

        const status = $('select[name="status"]').val()
            .map(item => {
                return $.fn.dataTable.util.escapeRegex(item);
            })
            .join('|');

        table.column(12).search(status ? status : '', true, false);

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
