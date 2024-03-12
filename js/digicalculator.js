var gettingPrices, $dc;
jQuery(document).ready(function ($) {
    $('#dc-form [name="papertype"], #dc-form [name="papertype_cover"]').change(function (e) {
        $dc.setWeights($(e.target).find('option:selected').attr('weights'), e)
    })
    $('#dc-form [name="product_size"]').change(function (e) {
        $dc.setSize($(this))
    });

    $('#dc-form [name="product_type"]').change(function (e) {
        $('#dc-form .dc-mainform').find('input,select').attr("disabled", true);
        $('#dc-form .dc-mainform').hide();

        var selected = $('#dc-form [name="product_type"]').val();
        $('#dc-form .' + selected).show();
        $('#dc-form .' + selected).find('input,select').attr("disabled", null);
    });


    $('#dc-form select, #dc-form input').change(function (e) {
        clearTimeout(gettingPrices);
        gettingPrices = setTimeout(() => {
            $('#dc-prices').empty();
            $dc.priceSelected();

            $dc.getPrices(function (resp) {
                $('#dc-prices').html(resp);
                if ($('#dc-prices [missing-fields]').length > 0) {
                    $('.missing').removeClass('missing');

                    var missingFields = $('#dc-prices [missing-fields]').attr('missing-fields').split(',');
                    for (const element of missingFields) {
                        $(`[name="${element}"]`).addClass('missing')
                    }
                    $('.missing').change(function () { $(this).removeClass('missing') })
                }
                $('#dc-prices tr td').click(function (e) {
                    $('#dc-prices .selected').removeClass('selected')
                    $(this).parent().addClass('selected');
                    $dc.priceSelected();
                })
            })
        }, 500)
    });

    $('#dc-calculate').click(function () {
        $('#dc-prices').empty();
        $dc.priceSelected();

        $dc.getPrices(function (resp) {
            $('#dc-prices').html(resp);
            $('#dc-prices tr td').click(function (e) {
                $('#dc-prices .selected').removeClass('selected')
                $(this).parent().addClass('selected');
                $dc.priceSelected();
            })
        })
    })
    $('#dc-order').click(function (e) {
        if (!$dc.priceSelected) {
            // e.preventDefault();
            return false;
        }
        $dc.setForm()
    })

    $dc = {
        putPrevData() {
            if (localStorage.dc_prevData != undefined && localStorage.dc_prevData != 'undefined') {
                const data = JSON.parse(localStorage.dc_prevData);
                if (data.global != undefined) {
                    for (const key in data.global) {
                        if (Object.hasOwnProperty.call(data.global, key)) {
                            const val = data.global[key];
                            $(`[name="${key}"]`).val(val);
                        }
                    }
                }
                for (const mform in data) {
                    var tdata = data[mform];
                    for (const key in tdata) {
                        if (Object.hasOwnProperty.call(tdata, key)) {
                            const val = tdata[key];
                            $(`.${mform} [name="${key}"]`).val(val);
                        }
                    }
                }
            }
            $('.input-sm').change();
            $('.input-sm').change(() => {
                $dc.savePrevData();
            });
        },
        savePrevData() {
            var data = { "global": {} };
            for (const mform of $('.dc-mainform')) {
                var $mform = $(mform), mformname = $mform.attr('group');
                data[mformname] = {};
                for (const inp of $mform.find('.input-sm')) {
                    const $inp = $(inp);
                    data[mformname][$inp.attr('name')] = $inp.val();
                }
            }
            for (const globals of $("#dc-form select.input-sm, #dc-form input.input-sm").not(".dc-mainform select, .dc-mainform input")) {
                const $globals = $(globals);
                data.global[$globals.attr('name')] = $globals.val();
            }

            localStorage.dc_prevData = JSON.stringify(data)
        },
        setWeights: function (weights, e) {
            var name = $(e.target).parent().parent().next().find('.input-sm').attr('name');
            if (weights == undefined) { return false; }
            weights = weights.split(',');
            $(`[name="${name}"] option`).hide();
            $(`[name="${name}"] option`).attr('disabled', true);
            for (const it of weights) {
                $(`[name="${name}"] option[value="${it}"]`).show();
                $(`[name="${name}"] option[value="${it}"]`).attr('disabled', null);
            }
            if ($(`[name="${name}"] option:selected`).attr('disabled') != undefined) {
                $(`[name="${name}"] option:not([disabled])`).eq(0).prop('selected', true);
            }
        },
        setSize: $t => {
            var mform = $t.parent().parent().parent().attr('group')
            console.log('setsize', `.${mform} .form-group.product_size_width`)
            if ($t.val() == 'unique') {
                $(`.${mform} .product_size_width, .${mform} .product_size_height`).attr('style',''); 
            } else {
                $(`.${mform} .product_size_width, .${mform} .product_size_height`).attr('style','display:none !important;');
            }
        },
        getObj: function () {
            const pt = $('[name="product_type"]').val(),
                ptc = "." + pt;
            var obj = {};
            for (const inp of $(`${ptc} .input-sm`)) {
                obj[$(inp).attr('name')] = $(inp).val();
            }
            for (const globals of $("#dc-form select.input-sm, #dc-form input.input-sm").not(".dc-mainform select, .dc-mainform input")) {
                obj[$(globals).attr('name')] = $(globals).val();
            }

            if (obj['dc-picked-quantity'] != undefined) { obj.quantity = obj['dc-picked-quantity']; }
            if (obj.addresses == undefined) { obj.addresses = 1; }
            if (obj.width == undefined) { obj.width = 1; }
            if (obj.height == undefined) { obj.height = 1; }
            if (obj.custom_width == undefined) { obj.custom_width = 1; }
            if (obj.custom_height == undefined) { obj.custom_height = 1; }
            if (obj.product_size == 'unique') { obj.product_size = `${$(ptc + ' [name="custom_width"]').val()}x${$(ptc + ' [name="custom_height"]').val()}`; }

            return obj;
            //To add: options
        },
        getQuoteObj: function () {
            var product_type = $('[name="product_type"]').val(),
                size = $(`.${product_type} [name="product_size"] option:selected`).text();
            if ($(`.${product_type} [name="product_size"]`).val() == 'unique') {
                size = `Afwijkend (${$(`.${product_type} [name="custom_width"]`).val()}mm x ${$(`.${product_type} [name="custom_height"]`).val()}mm)`;
            }
            var obj = [
                { "name": "Type product", "value": $('[name="product_type"] option:selected').text() },
                { "name": "Planoformaat (B * H mm)", "value": size }
            ];
            if (product_type == 'default') {
                obj.push({ "name": "Bedrukking", "value": $(`.${product_type} [name="printtype"] option:selected`).text() });
                obj.push({ "name": "Papiersoort", "value": $(`.${product_type} [name="papertype"] option:selected`).text() })
                obj.push({ "name": "Gewicht", "value": $(`.${product_type} [name="weight"] option:selected`).text() })

                if ($(`.${product_type} [name="option[1]"]`).val() != '') {
                    obj.push({ "name": "Afwerking", "value": $(`.${product_type} [name="option[1]"] option:selected`).text() })
                }
                if ($(`.${product_type} [name="option[4]"]`).val() != '') {
                    obj.push({ "name": "Luxe afwerking", "value": $(`.${product_type} [name="option[4]"] option:selected`).text() })
                }
            } else {
                obj.push({ "name": "Bedrukking omslag", "value": $(`.${product_type} [name="printtype_cover"] option:selected`).text() })
                obj.push({ "name": "Papiersoort omslag", "value": $(`.${product_type} [name="papertype_cover"] option:selected`).text() })
                obj.push({ "name": "Gewicht omslag", "value": $(`.${product_type} [name="weight_cover"] option:selected`).text() })

                obj.push({ "name": "Bedrukking binnenwerk", "value": $(`.${product_type} [name="printtype"] option:selected`).text() })
                obj.push({ "name": "Papiersoort binnenwerk", "value": $(`.${product_type} [name="papertype"] option:selected`).text() })
                obj.push({ "name": "Gewicht binnenwerk", "value": $(`.${product_type} [name="weight"] option:selected`).text() })
                
                if ($(`.${product_type} [name="option[1]"]`).val() != '') {
                    obj.push({ "name": "Afwerking", "value": $(`.${product_type} [name="option[1]"] option:selected`).text() })
                }
                if ($(`.${product_type} [name="option[2]"]`).val() != '') {
                    obj.push({ "name": "Luxe afwerking omslag", "value": $(`.${product_type} [name="option[2]"] option:selected`).text() })
                }
                if ($(`.${product_type} [name="option[3]"]`).val() != '') {
                    obj.push({ "name": "Luxe afwerking binnenwerk", "value": $(`.${product_type} [name="option[3]"] option:selected`).text() })
                }
            }
            return obj;
        },
        getPrices: function (cb) {
            const data = $dc.getObj();
            $.post(dc_ajax.ajaxurl, {
                action: 'DC-getPrices',
                data: data,
                nextNonce: dc_ajax.nextNonce
            }, function (resp) {
                cb(resp);
            }, 'html');
        },
        setForm: function (id, cb) {
            const cc = $('[name="product_type"]').val();
            if ($(`.${cc} [name="product_size"]`).val() != "unique") {
                var vals = $(`.${cc} [name="product_size"]`).val().split('x')
                $(`.${cc} [name="custom_width"]`).val(vals[0])
                $(`.${cc} [name="custom_height"]`).val(vals[1])
            }
        },
        priceSelected: function () {
            if ($('#dc-prices .selected').length == 1) {
                $('[name="add-to-cart"]').attr('disabled', null);
                $('[name="add-to-quotations"]').attr('disabled', null);
                $('[name="quantity"]').val($('#dc-prices .selected [name="dc-quantity"]').text());
                return true;
            } else {
                $('[name="add-to-cart"]').attr('disabled', true);
                $('[name="add-to-quotations"]').attr('disabled', true);
                $('[name="quantity"]').val(null);
                return false;
            }
        },
        saveQuotation: function (e) {
            //popup;
            if (e != undefined) {
                e.preventDefault();
            }
            dc_openPopup(function ($p) {
                var desc = $dc.buildDescription(),
                    product_obj = $dc.getObj();
                product_obj.url = location.href;
                product_obj.quantity = $('#dc-prices .selected [name="dc-quantity"]').text().trim();
                product_obj['dc-picked-quantity'] = product_obj.quantity;
                product_obj['add-to-cart'] = $('[name="add-to-cart"]').attr('value');

                $p.append(`<p>${desc}.</p>`);
                $p.append('<p>Vul een referentie voor uw offerte in.</p>');
                $p.append('<input placeholder="Referentie" id="ext-ref" />')
                $p.append('<button class="button wp-element-button">Opslaan</button>');

                $p.find('button').off('click').click(function () {
                    //ajax call to save;
                    var tdata = { "product_obj": product_obj }
                    tdata.extref = $p.find('#ext-ref').val();
                    tdata.product_description = desc;
                    tdata.quotation_obj = $dc.getQuoteObj();
                    $.post(dc_ajax.ajaxurl, {
                        action: 'DC-saveQuotation',
                        data: tdata,
                        nextNonce: dc_ajax.nextNonce
                    }, function (resp) {
                        //close popup? refresh page?
                        console.log(resp);
                        location.reload();
                    }, 'html');
                })
            })
        },
        buildDescription: function () {
            if ($('[name="product_type"]').val() == 'default') {
                var desc = [
                    "Standaard",
                    $(`[group="${$('[name="product_type"]').val()}"] [name="product_size"] option:selected`).text(),
                    $(`[group="${$('[name="product_type"]').val()}"] [name="weight"] option:selected`).text() + " " + $(`[group="${$('[name="product_type"]').val()}"] [name="papertype"] option:selected`).text(),
                    $(`[group="${$('[name="product_type"]').val()}"] [name="printtype"] option:selected`).text()
                ];
            } else {
                var desc = [
                    "Brochure",
                    $(`[group="${$('[name="product_type"]').val()}"] [name="product_size"] option:selected`).text(),
                    $(`[group="${$('[name="product_type"]').val()}"] [name="weight"] option:selected`).text() + " " + $(`[group="${$('[name="product_type"]').val()}"] [name="papertype"] option:selected`).text(),
                    $(`[group="${$('[name="product_type"]').val()}"] [name="printtype"] option:selected`).text(),
                    "Omslag: " + $(`[group="${$('[name="product_type"]').val()}"] [name="weight_cover"] option:selected`).text() + " " + $(`[group="${$('[name="product_type"]').val()}"] [name="papertype_cover"] option:selected`).text(),
                    $(`[group="${$('[name="product_type"]').val()}"] [name="printtype_cover"] option:selected`).text()
                ];
            }
            if ($(`[group="${$('[name="product_type"]').val()}"] [name="option[1]"]`).val() != '' && $(`[group="${$('[name="product_type"]').val()}"] [name="option[1]"]`).val() != undefined) {
                desc.push($(`[group="${$('[name="product_type"]').val()}"] [name="option[1]"] option:selected`).text());
            }
            if ($(`[group="${$('[name="product_type"]').val()}"] [name="option[2]"]`).val() != '' && $(`[group="${$('[name="product_type"]').val()}"] [name="option[2]"]`).val() != undefined) {
                desc.push($(`[group="${$('[name="product_type"]').val()}"] [name="option[2]"] option:selected`).text());
            }
            if ($(`[group="${$('[name="product_type"]').val()}"] [name="option[3]"]`).val() != '' && $(`[group="${$('[name="product_type"]').val()}"] [name="option[3]"]`).val() != undefined) {
                desc.push($(`[group="${$('[name="product_type"]').val()}"] [name="option[3]"] option:selected`).text());
            }
            if ($(`[group="${$('[name="product_type"]').val()}"] [name="option[4]"]`).val() != '' && $(`[group="${$('[name="product_type"]').val()}"] [name="option[4]"]`).val() != undefined) {
                desc.push($(`[group="${$('[name="product_type"]').val()}"] [name="option[4]"] option:selected`).text());
            }
            if ($(`[name="versions"]`).val() == 1) {
                desc.push('1 versie')
            } else {
                desc.push($(`[name="versions"]`).val() + ' versies');
            }
            return desc.join(', ');
        },
        postDcIdToCart: function (id) {
            $.post(dc_ajax.ajaxurl, {
                action: 'DC-getQuotation',
                data: { "id": id },
                nextNonce: dc_ajax.nextNonce
            }, function (resp) {
                //close popup? refresh page?
                console.log(resp);
                $.post(resp.url, resp, (r, e) => {
                    console.log(r);
                    console.log(e);
                    location.reload();
                });
            }, 'json');

        }
    }

    $dc.putPrevData();
})

function dc_openPopup(cb) {
    var $ = jQuery;
    var $fade = $("<div id='firstvisit-fade'/>"),
        $popup = $('<div id="firstvisit-popup"/>');
    $fade.append($popup)

    $('body').prepend($fade)
    $popup.close = function () {
        $fade.fadeOut(300, function () {
            $fade.remove()
        })
    }
    $fade.click(function (e) {
        if (e.target == this) {
            $popup.close()
        }
    })
    cb($popup);
}