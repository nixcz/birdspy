/* DataTables Buttons */

import $ from "jquery";

export const createButtons = table => {

    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: 'copy',
                titleAttr: 'Copy Values',
                exportOptions: {
                    orthogonal: 'export',
                    columns: '.export',
                },
            },
            {
                extend: 'print',
                titleAttr: 'Print Table',
                exportOptions: {
                    orthogonal: 'display',
                    columns: '.export',
                },
            },
            {
                extend: 'csv',
                titleAttr: 'Export to .csv file',
                exportOptions: {
                    orthogonal: 'export',
                    columns: '.export',
                },
            },
        ],
    });

    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                text: 'Filter',
                titleAttr: 'Show Filter',
                action: function (e, dt, button, config) {
                    $('.table-filter').modal({
                        backdrop: true,
                        keyboard: false,
                    });
                },
            },
            /*{
                text: 'Clear',
                titleAttr: 'Clear Filter',
                action: function (e, dt, button, config) {
                    clearFilter(dt);
                },
                //enabled: false
            },*/
            {
                text: 'Reset',
                titleAttr: 'Reset Settings',
                action: function (e, dt, button, config) {
                    resetSettings(dt);
                }
            },
            {
                extend: 'colvis',
                text: 'Visibility',
                titleAttr: 'Columns Visibility',
                columns: [':not(.persistent)'],
                //postfixButtons: ['colvisRestore']
            },
            /*{
                text: 'Reset',
                titleAttr: 'Reset Settings',
                action: function (e, dt, button, config) {
                    // dt.ajax.reload();

                    const length = 10;

                    // Reset column filtering
                    $('.column-filter input').val('').change();
                    $('.column-filter select').val('').change();
                    // Reset column ordering
                    //dt.colReorder.reset();

                    // Reset hidden columns to defaults
                    //table.column(0).visible(false);
                    //dt.columns(config.show).visible(true);

                    // Reset main search, order and redraw
                    //.order(table.settings().order())

                    $('.dataTables_length select').val(length).change();

                    dt
                        //.order(defaultOrder)
                        .page.len(length)
                        .search('')
                        .draw();
                }
            }*/
        ],
    });

    $('.export-buttons', table.table().container())
        .append(table.buttons(0, null).container());

    $('.filter-buttons', table.table().container())
        .append(table.buttons(1, null).container());
};

export const clearFilter = dt => {
    // dt.ajax.reload();

    // Reset column filtering
    $('.filter-dt input').val('');
    $('.filter-dt select').val('').change();
    //$('.filter-dt .selectpicker').selectpicker('deselectAll');

    dt.columns().every(function () {
        this.search('');
    });

    // Reset main search filter and redraw
    dt.search('').draw();
};

export const resetSettings = dt => {
    dt.state.clear();
    window.location.reload();
};
