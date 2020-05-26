/*
 * DataTables
 */

import $ from 'jquery';

import 'bootstrap-select';

import 'datatables.net-bs4';
import 'datatables.net-colreorder-bs4';
import 'datatables.net-plugins/sorting/ip-address';
import 'datatables.net-plugins/sorting/natural';
import 'datatables.net-buttons-bs4';
import 'datatables.net-buttons/js/buttons.colVis';
import 'datatables.net-buttons/js/buttons.html5';
import 'datatables.net-buttons/js/buttons.print';

import { appendCached } from "./Components/DataTablesHelpers";

$.extend($.fn.dataTable.defaults, {
    language: {
        decimal: ",",
        thousands: "&nbsp;"
    },
    colReorder: true,
    deferRender: true,
    processing: true,
    serverSide: false,
    lengthMenu: [
        [10, 25, 50, 100], [10, 25, 50, 100]
    ],
    //orderCellsTop: true,
    dom:
        "<'d-lg-flex flex-row'<'flex-fill'l><f><'ml-2 export-buttons'><'ml-2 filter-buttons'>>" +
        "<'row'<'col-12'tr>>" +
        "<'row align-items-center'<'col-md-12 col-lg-5 order-lg-1 order-12'i><'col-md-12 col-lg-7 order-lg-12 order-1'p>>",
    renderer: 'bootstrap',
    preDrawCallback: function (settings) {
        $('.dataTables_length').find('select').selectpicker({ width: 'fit' });
    },
    stateSave: true,
    stateDuration: 0,
    stateSaveCallback: function (settings, data) {
        if (data.search) {
            data.search.search = '';
        }

        if (data.columns) {
            data.columns.map(column => {
                column.search.search = '';
            });
        }

        localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data))
    },
    stateLoadCallback: function (settings) {
        return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance))
    },
    initComplete: function (settings, json) {
        appendCached(json);
    }
});

$.extend(true, $.fn.dataTable.Buttons.defaults, {
    dom: {
        container: {
            className: 'dt-buttons btn-group btn-group-sm flex-wrap'
        },
        button: {
            className: 'btn btn-sm btn-outline-secondary'
        },
        collection: {
            tag: 'div',
            className: 'dropdown-menu',
            button: {
                tag: 'a',
                className: 'dt-button dropdown-item',
                active: 'active',
                disabled: 'disabled'
            }
        }
    },
    buttonCreated: function (config, button) {
        return config.buttons ? $('<div class="btn-group"/>').append(button) : button;
    }
});
