$(document).ready(function() {

    $('form#stock_adjustment_form').validate();
    inventory_count_table = $('#inventory_count_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/inventory-count',
        columnDefs: [
            {
                targets: 0,
               // orderable: false,
                searchable: false,
            },
        ],
        aaSorting: [[2, 'desc']],
        columns: [
            { data: 'action', name: 'action' },
            { data: 'date', name: 'date' },
            { data: 'count_reference', name: 'count_reference' },
            { data: 'count_description', name: 'count_description' },
            { data: 'location', name: 'location' },
            { data: 'categories', name: 'categories' },
            { data: 'sub_categories', name: 'sub_categories' },
            { data: 'brands', name: 'brands' },
            { data: 'type', name: 'type' },
            { data: 'status', name: 'status' }
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#inventory_count_table'));
        },
    });

    $('#count_type').change(function() {
        const count_type = $(this).val();
        if(count_type === 'partial') {
            $('#partial_area').show();
        } else {
            $('#partial_area').hide();
            // $('#categories').select2('val', []);
            // $('#brands').select2('val', []);
            // $('#sub_categories').select2('val', []);
            $('.select2-multiple').val([]).change();
        }
    });

    $('.select2-multiple').select2();

    $(document).on('change', '#categories', function() {
        get_sub_categories($(this).find(':selected').data('cat_id'));
    });
   
    function get_sub_categories(cat) {
        $.ajax({
            method: 'POST',
            url: '/products/get_sub_categories',
            dataType: 'html',
            data: { cat_id: cat },
            success: function(result) {
                if (result) {
                    $('#sub_categories').html(result.replace('<option value="">None</option>', ''));
                }
            },
        });
    }

    $(document).on('click', '.download_inventory_count', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var count_reference = $(this).data('count_reference');
        $.ajax({
            type: "GET",
            url: url,
            xhrFields: {
                responseType: 'blob',
            },
            success: function (data) {
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(data);
                link.download = count_reference + `.csv`;
                link.click();
            }
        });
    });

    $(document).on('click', '.upload_final_file', function(e) {
        var count_reference = $(this).data('count_reference');
        var final_file_upload_action = '/inventory-count/upload-final-file/'+count_reference;
        $('#finalize form').attr('action', final_file_upload_action);
    });
    


});


$(document).on('shown.bs.modal', '.view_modal', function() {
    __currency_convert_recursively($('.view_modal'));
});