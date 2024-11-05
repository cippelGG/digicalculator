jQuery(document).ready(function ($) {
    // Dropzone.options.mydz = {
    //     dictDefaultMessage: "your custom message"
    // };    

    handle_dc_upload_pond($);
    handle_dc_delete_file($);

    $( document.body ).on( 'updated_cart_totals', function(){
        handle_dc_upload_pond($);
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
            <div id="dcDropzone-${id}" class="dropzone"> 
                <div class="dz-message" data-dz-message><span>Klik hier om een bestand toe te voegen <br/> <small>of sleep een bestand op dit vlak</small> </span></div>  
            </div>
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
            } ,
            error: function(file, resp){
                var tpz = this,
                    $error = $(`<div class="woocommerce-error">${file.name}: ${resp}</div>`);
                $(`#dcDropzone-${id}`).before($error)
                $error.click(function(){
                    $(this).remove();
                    tpz.removeFile(file);
                })
            }
        });
    })
}

function handle_dc_upload_pond($){
    $('.upload-new-file').off('click').click(function (e) {
        $(this).hide().off('click').click(function(e){
            //Hide button after first click. Also block the interaction to be sure
            e.preventDefault();
        });
        e.preventDefault();
        //Add form
        var id = $(this).attr('data-id');
        console.log('add form');

        $(this).after(`<div class="pww-uploader">
            <input type="file" class="filepond" id="dcDropzone-${id}">
        </div>`);
        var $pond = $(`#dcDropzone-${id}`);
        $pond.filepond({
            credits: false,
                "labelIdle": "Sleep bestanden hierheen of klik om te <span class=\"filepond--label-action\"> Bladeren <\/span>",
                "labelInvalidField": "Veld bevat ongeldige bestanden",
                "labelFileWaitingForSize": "Wachten op maat",
                "labelFileSizeNotAvailable": "Maat niet beschikbaar",
                "labelFileLoading": "Bezig met laden",
                "labelFileLoadError": "Fout tijdens laden",
                "labelFileProcessing": "Uploaden",
                "labelFileProcessingComplete": "Upload compleet",
                "labelFileProcessingAborted": "Upload geannuleerd",
                "labelFileProcessingError": "Fout tijdens uploaden",
                "labelFileProcessingRevertError": "Fout tijdens terugzetten",
                "labelFileRemoveError": "Fout tijdens verwijderen",
                "labelTapToCancel": "tik om te annuleren",
                "labelTapToRetry": "tik om opnieuw te proberen",
                "labelTapToUndo": "tik om ongedaan te maken",
                "labelButtonRemoveItem": "Verwijderen",
                "labelButtonAbortItemLoad": "Afbreken",
                "labelButtonRetryItemLoad": "Opnieuw proberen",
                "labelButtonAbortItemProcessing": "Annuleren",
                "labelButtonUndoItemProcessing": "Ongedaan maken",
                "labelButtonRetryItemProcessing": "Opnieuw proberen",
                "labelButtonProcessItem": "Uploaden",
                "labelMaxFileSizeExceeded": "Bestand is te groot",
                "labelMaxFileSize": "Maximale bestandsgrootte is 128MB",
            server: {
                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                    const formData = new FormData();

                    formData.append(fieldName, file, file.name);
                    formData.append('action', 'DC-uploadfile-pond');
                    formData.append('cart_item_id', id);
                    formData.append('nextNonce', dc_ajax.nextNonce);
        
                    const request = new XMLHttpRequest();
                    request.open('POST', dc_ajax.ajaxurl);
        
                    request.upload.onprogress = (e) => {
                        progress(e.lengthComputable, e.loaded, e.total);
                    };
        
                    request.onload = function () {
                        if (request.status >= 200 && request.status < 300) {
                            console.log(request)
                            console.log("This upload object",$(e.target))
                            var resp = JSON.parse(request.responseText);
                            if( $(e.target).parent().find('.pww-product-files-wrap').length == 0 ){
                                $(e.target).parent().append('<div class="pww-product-files-wrap"><br><span>Drukbestanden:<span><br><div class="pww-product-files-list"></div></span></span></div>')
                            }
                            $(e.target).parent().find('.pww-product-files-list').append(`<a
                                href="${resp['url']}"
                                target="_blank">${resp['name']}
                            </a> - 
                            <a class="pww-file-delete-link dc-delete_upload" 
                                data-key="${resp['id']}" 
                                data-file="${resp['file']}"
                                >verwijderen
                            </a>`);

                            refresh_shoppingcart_when_done($);
                        } else {
                            // Can call the error method if something is wrong, should exit after
                            error('oh no');
                        }
                    };
        
                    request.send(formData);
                }
            }    
        });
    })
}

function handle_dc_delete_file($){
    $('.dc-delete_upload').off('click').click(function(){
        var id = $(this).attr('data-key'),
            file = $(this).attr('data-file'),
            $t = $(this).parent();
        $t.addClass('deleting');
        var url = `${dc_ajax.ajaxurl}?action=DC-deletefile&cart_item_id=${id}&file=${file}`;
        console.log(url);
        $.get(`${dc_ajax.ajaxurl}?action=DC-deletefile&cart_item_id=${id}&file=${file}`,function(){
            $t.remove();
            refresh_shoppingcart_when_done($);
        })
    });
}

function refresh_shoppingcart_when_done($){
    if ($('.deleting').length == 0 &&
        ( $('.filepond--file-status-main').length == $('.filepond--file-status-main:contains("Uploading 100%")').length 
        || $('.filepond--file-status-main').length == $('.filepond--file-status-main:contains("Uploaden 100%")').length 
        || $('.filepond--file-status-main').length == $("[data-filepond-item-state='processing-complete']").length )
    ){
        $("[name='update_cart']").removeAttr('disabled');
        setTimeout(function(){
            $("[name='update_cart']").trigger('click');
        },100)
    } else {
        setTimeout(function () {
            refresh_shoppingcart_when_done($);
        }, 500)
    }
}

function dc_resendxml(){
    $ = jQuery;
    var url = `${dc_ajax.ajaxurl}?action=DC-resendxml`;
    $.get(url,function(r){
        console.log(r)
    })
}