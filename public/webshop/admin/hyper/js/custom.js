/**
 * @summary     Saját scriptek
 * @description Paginate, search and order HTML tables
 * @version     0.1
 * @author      Difiori
 *
 */



$(document).ready( function () {
    $.extend( $.fn.dataTable.defaults, {
        order: [],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Hungarian.json"
        },
        // stateSave: true
        // buttons: ['print', 'excel', 'pdf']
    } );

    // $('#inventory-supply-list-Vágott').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2,3,4]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    //
    // });
    // $('#inventory-supply-list-Cserepes').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2,3,4]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    // });
    // $('#inventory-supply-list-Zöldek').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2,3,4]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    // });
    // $('#inventory-supply-list-Orhideák').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2,3,4]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    // });
    // $('#inventory-supply-list-Egyéb').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2,3,4]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    // });

    // /**
    //  * Inventory Waste tables
    //  */
    // $('#inventory-waste-list-Vágott').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    //
    // });
    // $('#inventory-waste-list-Cserepes').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    // });
    // $('#inventory-waste-list-Zöldek').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    // });
    // $('#inventory-waste-list-Orhideák').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    // });
    // $('#inventory-waste-list-Egyéb').DataTable({
    //     paging: false,
    //     columnDefs: [{
    //         orderable : false,
    //         targets : [1,2]
    //     }],
    //     dom: "<'row m-2'<'col-sm-12 col-md-12'l><'col-sm-12 col-md-12'f>>",
    // });



    $('#product-list').DataTable({paging: false});

    $('#inventory-product-list').DataTable( {
        order: [],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Hungarian.json"
        },
        buttons: [
            'print', 'excel', 'pdf'
        ]
    } );
} );


