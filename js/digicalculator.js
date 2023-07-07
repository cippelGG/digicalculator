var gettingPrices;
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
                if( $('#dc-prices [missing-fields]').length > 0 ){
                    $('.missing').removeClass('missing');
                    
                    var missingFields = $('#dc-prices [missing-fields]').attr('missing-fields').split(',');
                    for (const element of missingFields) {
                        $(`[name="${element}"]`).addClass('missing')
                    }
                    $('.missing').change(function(){$(this).removeClass('missing')})
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

    const $dc = {
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
            console.log('setsize',`.${mform} .form-group.product_size_width`) 
            if ($t.val() == 'unique') {
                $(`.${mform} .product_size_width, .${mform} .product_size_height`).show();
            } else {
                $(`.${mform} .product_size_width, .${mform} .product_size_height`).hide();
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
        getPrices: function (cb) {
            const data = $dc.getObj();
            $.post(dc_ajax.ajaxurl, {
                action: 'DC-getPrices',
                data: data,
                nextNonce: dc_ajax.nextNonce
            }, function (resp) {
                cb(resp);
            },'html');
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
                $('[name="quantity"]').val($('#dc-prices .selected [name="dc-quantity"]').text());
                return true;
            } else {
                $('[name="add-to-cart"]').attr('disabled', true);
                $('[name="quantity"]').val(null);
                return false;
            }
        }
    }

    $dc.putPrevData();
})