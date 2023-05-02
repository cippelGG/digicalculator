jQuery(document).ready(function ($) {
    handle_dc_upload($);
    handle_dc_delete_file($);

    $( document.body ).on( 'updated_cart_totals', function(){
        handle_dc_upload($);
        handle_dc_delete_file($);
        //re-do your jquery
    });
})

function handle_dc_upload($) {
    $('.upload-new-file').off('click').click(function (e) {
        $(this).hide().off('click').click(function(e){
            //Hide button after first click. Also block the interaction to be sure
            e.preventDefault();
        });
        e.preventDefault();
        //Add form
        var id = $(this).attr('data-id')
        console.log('add form')
        $(this).after(`
            <div id="dcDropzone-${id}" class="dropzone"/>
        `);
        $(`div#dcDropzone-${id}`).dropzone({ 
            url: dc_ajax.ajaxurl,
            params: {
                action: "DC-uploadfile",
                cart_item_id: id,
                nextNonce: dc_ajax.nextNonce
            }, // The name that will be used to transfer the file
            paramName: "file", // The name that will be used to transfer the file
            maxFilesize: 150, // MB
            success: function (file, resp) {
                try {
                    var resp = JSON.parse(resp);
                    $(e.target).parent().find('.dc-shoppingcart_table').append(`<tr>
                        <td>${resp['name']}</td>
                        <td><a class="fas fa-eye dc-view_upload" href="${resp['url']}" target="_blank"></a></td>
                        <td><i class="fas fa-trash-alt dc-delete_upload" data-key="${resp['id']}" data-file="${resp['file']}"></i></td>                    
                    </tr>`);

                    //empty the uploader and add the just oploaded files;
                    refresh_shoppingcart_when_done($);
                    this.removeFile(file);

                } catch (error) {
                    console.error(resp);
                    console.error(file);
                    alert(`Problem with uploading ${JSON.stringify(file)}`);
                    this.removeFile(file);
                }
            } 
        });
    })
}

function handle_dc_delete_file($){
    $('.dc-delete_upload').off('click').click(function(){
        var id = $(this).attr('data-key'),
            file = $(this).attr('data-file'),
            $t = $(this).parent().parent();
        $t.addClass('deleting')
        var url = `${dc_ajax.ajaxurl}?action=DC-deletefile&cart_item_id=${id}&file=${file}`;
        console.log(url)
        $.get(`${dc_ajax.ajaxurl}?action=DC-deletefile&cart_item_id=${id}&file=${file}`,function(){
            $t.remove();
            refresh_shoppingcart_when_done($);
        })
    });
}

function refresh_shoppingcart_when_done($){
    if( $('.deleting').length == 0 && $('.dz-preview.dz-file-preview').length == 0 ){
        $( "[name='update_cart']" ).removeAttr( 'disabled' );
        $( "[name='update_cart']" ).trigger( 'click' );
    } else {
        setTimeout(function(){
            refresh_shoppingcart_when_done($);
        },500)
    }
}