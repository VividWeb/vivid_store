var vividStore = {

openModal: function(content) {
    if ($( ".whiteout" ).length ) {
        $( ".whiteout" ).empty().html(content);
    } else {
        $("body").append("<div class='whiteout'>"+content+"</div>");

        $(".whiteout").click(function(e){
            if(e.target != this) return;  // only allow the actual whiteout background to close the dialog
            vividStore.exitModal();
        });

        $(document).keyup("keyup.vividwhiteout", function(e){
            if(e.keyCode === 27) {
                vividStore.exitModal();
                $(document).unbind("keyup.vividwhiteout");
            }
        });
    }
}
,
waiting: function(){
    vividStore.openModal("<div class='vivid-store-spinner-container'><div class='vivid-store-spinner'></div></div>");
},

exitModal: function(){
    $(".whiteout").remove();
},


//PRODUCT LIST
    
    //Open Product Modal
    productModal: function(pID){
        vividStore.waiting();
        $.ajax({
           url: vividStore.URLs.ProductModal,
           data: {pID: pID},
           type: 'post',
           success: function(modalContent){
               vividStore.openModal(modalContent);
           },
           error: function(){
               alert("something went wrong");
           }
        });
    },

//SHOPPING CART

    displayCart: function(res) {
        $.ajax({
            type: "POST",
            data: res,
            url: vividStore.URLs.Cart+'/getmodal',
            success: function(data){
                vividStore.openModal(data);
            }
        });
    },



    //Add Item to Cart
    addToCart: function(pID, type){
        var form;
        if(type =='modal'){
            form = $('#form-add-to-cart-modal-'+pID);
        } else if (type == 'list') {
            form = $('#form-add-to-cart-list-'+pID);
        } else {
            form = $('#form-add-to-cart-'+pID);
        }
        var qty = $(form).find('.product-qty').val();
        if(qty > 0){
            var cereal = $(form).serialize(); //haha, cereal.
            vividStore.waiting();
            $.ajax({ 
                url: vividStore.URLs.Cart+"/add",
                data: cereal,
                type: 'post',
                success: function(data) {
                    var res = jQuery.parseJSON(data);

                    if (res.product.pAutoCheckout == '1') {
                        window.location.href = vividStore.URLs.Checkout;
                        return false;
                    }

                    vividStore.displayCart(res);

                    $.ajax({
                       url: vividStore.URLs.Cart+'/getTotalItems',
                       success: function(itemCount){
                           $(".vivid-store-utility-links .items-counter").text(itemCount);
                           if (itemCount > 0) {
                               $(".vivid-store-utility-links").removeClass('vivid-cart-empty');
                           }
                       } 
                    });
                    $.ajax({
                       url: vividStore.URLs.Cart+'/getSubTotal',
                       success: function(subTotal){
                           $(".vivid-store-utility-links .total-cart-amount").text(subTotal);
                       } 
                    });
                }
            });
        } else {
            alert(QTYMESSAGE);
        }
    },
    
    //Update Item in Cart
    updateItem: function(instanceID, modal){
        var qty = $("*[data-instance-id='"+instanceID+"']").find(".cart-list-product-qty input").val();
        vividStore.waiting();
        $.ajax({ 
            url: vividStore.URLs.Cart+"/update",
            data: {instance: instanceID, pQty: qty},
            type: 'post',
            success: function(data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    vividStore.displayCart(res);
                }

                $.ajax({
                    url: vividStore.URLs.Cart + '/getTotalItems',
                    success: function (itemCount) {
                        $(".vivid-store-utility-links .items-counter").text(itemCount);

                        if (itemCount == 0) {
                            $(".vivid-store-utility-links .items-counter").text(0);
                            $(".vivid-store-utility-links .total-cart-amount").text("");
                            $(".vivid-store-utility-links").addClass('vivid-cart-empty');
                        } else {
                            $.ajax({
                                url: vividStore.URLs.Cart + "/getSubTotal",
                                success: function (total) {
                                    $(".cart-grand-total-value").text(total);
                                }
                            });

                            $.ajax({
                                url: vividStore.URLs.Cart + '/getSubTotal',
                                success: function (subTotal) {
                                    $(".vivid-store-utility-links .total-cart-amount").text(subTotal);
                                }
                            });
                        }
                    }
                });

            }
        }); 
    },
    
    //Remove Item in Cart
    removeItem: function(instanceID, modal){
        vividStore.waiting();
        $.ajax({ 
            url: vividStore.URLs.Cart+"/remove",
            data: {instance: instanceID},
            type: 'post',
            success: function(data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    vividStore.displayCart(res);
                }

                $.ajax({
                    url: vividStore.URLs.Cart + '/getTotalItems',
                    success: function (itemCount) {
                        $(".vivid-store-utility-links .items-counter").text(itemCount);

                        if (itemCount == 0) {
                            $(".vivid-store-utility-links .items-counter").text(0);
                            $(".vivid-store-utility-links .total-cart-amount").text("");
                            $(".vivid-store-utility-links").addClass('vivid-cart-empty');
                        } else {
                            $.ajax({
                                url: vividStore.URLs.Cart + "/getSubTotal",
                                success: function (total) {
                                    $(".cart-grand-total-value").text(total);
                                }
                            });

                            $.ajax({
                                url: vividStore.URLs.Cart + '/getSubTotal',
                                success: function (subTotal) {
                                    $(".vivid-store-utility-links .total-cart-amount").text(subTotal);
                                }
                            });
                        }
                    }
                });
            }
        }); 
    },
    
    //Clear the Cart
    clearCart: function(modal){
         $.ajax({ 
             url: vividStore.URLs.Cart+"/clear",
             success: function(data) {
                 if (modal) {
                     var res = jQuery.parseJSON(data);
                     vividStore.displayCart(res);
                 }

                 $.ajax({
                     url: vividStore.URLs.Cart+"/getSubTotal",
                     success: function(total){
                         $(".cart-grand-total-value").text(total);
                         $(".cart-page-cart-list-item").remove();
                         $(".vivid-store-utility-links .items-counter").text(0);
                         $(".vivid-store-utility-links .total-cart-amount").text("");
                         $(".vivid-store-utility-links").addClass('vivid-cart-empty');
                     }
                 });

             }
        });
    },

//CHECKOUT

    loadViaHash: function(){
        var hash = window.location.hash;
        hash = hash.replace('#','');
        if(hash != ""){
            //$(".checkout-form-group .checkout-form-group-body").hide();
            $(".active-form-group").removeClass('active-form-group');
            var pane = $("#checkout-form-group-"+hash);
            pane.addClass('active-form-group');

            $('html, body').animate({
                scrollTop: pane.offset().top
            });
        }
    },
    //loadViaHash();

    updateBillingStates: function(load){
        var countryCode = $("#checkout-billing-country").val();
        var selectedState;
        if (load){
            selectedState = $("#checkout-saved-billing-state").val();
        } else {
            selectedState = '';
        }
       
        $.ajax({
           url: vividStore.URLs.Checkout+"/getstates",
           type: 'post',
           data: {country: countryCode, selectedState: selectedState, type: "billing"},
           success: function(states){
                $("#checkout-billing-state").replaceWith(states);
           } 
        });
    },
    
    
    
    updateShippingStates: function(load){
        var countryCode = $("#checkout-shipping-country").val();
        var selectedState;
        if (load){
            selectedState = $("#checkout-saved-shipping-state").val();
        } else {
            selectedState = '';
        }

        $.ajax({
           url: vividStore.URLs.Checkout+"/getstates",
           type: 'post',
           data: {country: countryCode, selectedState: selectedState, type: "shipping"},
           success: function(states){
                $("#checkout-shipping-state").replaceWith(states);
           } 
        });
    },


    nextPane: function(obj){
        if($(obj)[0].checkValidity()){
            var pane = $(obj).closest(".checkout-form-group").find('.checkout-form-group-body').parent().next();
            $('.active-form-group').removeClass('active-form-group');
            pane.addClass('active-form-group');
            $(obj).closest(".checkout-form-group").addClass('checkout-form-group-complete');

            $('html, body').animate({
                scrollTop: pane.offset().top
            });

            pane.find('input:first-child').focus();
        }
    },
    
    showShippingMethods: function(){
        $.ajax({
            url: vividStore.URLs.Checkout+"/getShippingMethods",
            success: function(html){
                $("#checkout-shipping-method-options").html(html);
            }
        });  
    },
    
    showPaymentForm: function(){
        var pmID = $("#checkout-payment-method-options input[type='radio']:checked").attr('data-payment-method-id');
        $('.payment-method-container').addClass('hidden');
        $(".payment-method-container[data-payment-method-id='"+pmID+"']").removeClass('hidden');
    },

    saveAddressInfo: function(addressType){
        var email = "";
        if(addressType=='billing') {
            email = $("#email").val();
        }
        var firstName = $("#checkout-"+addressType+"-first-name").val();
        var lastName = $("#checkout-"+addressType+"-last-name").val();
        var companyName = $("#checkout-"+addressType+"-company-name").val();
        var phone = $("#checkout-"+addressType+"-phone").val();
        var address1 = $("#checkout-"+addressType+"-address-1").val();
        var address2 = $("#checkout-"+addressType+"-address-2").val();
        var country = $("#checkout-"+addressType+"-country").val();
        var city = $("#checkout-"+addressType+"-city").val();
        var state = $("#checkout-"+addressType+"-state").val();
        var postal = $("#checkout-"+addressType+"-zip").val();
        $("#checkout-form-group-"+addressType+" .checkout-form-group-body .checkout-errors").remove();

        vividStore.waiting();
        var obj = $("#checkout-form-group-"+addressType);
        $.ajax({
            url: vividStore.URLs.Checkout + "/updater",
            type: 'post',
            data: {
                adrType: addressType,
                email: email,
                fName: firstName,
                lName: lastName,
                cName: companyName,
                phone: phone,
                addr1: address1,
                addr2: address2,
                count: country,
                city: city,
                state: state,
                postal: postal
            },
            success: function (result) {
                //var test = null;
                var response = JSON.parse(result);
                if (response.error == false) {
                    $(".whiteout").remove();
                    obj.find('.checkout-form-group-summary .summary-name').html(response.first_name + ' ' + response.last_name);
                    obj.find('.checkout-form-group-summary .summary-company').html(companyName);
                    obj.find('.checkout-form-group-summary .summary-phone').html(response.phone);
                    if(addressType=='billing') {
                        obj.find('.checkout-form-group-summary .summary-email').html(response.email);
                    }
                    obj.find('.checkout-form-group-summary .summary-address').html(response.address);
                    vividStore.nextPane(obj);
                    //update tax
                    $.ajax({
                        url: vividStore.URLs.Cart + "/getTaxTotal",
                        success: function (results) {
                            var taxes = JSON.parse(results);
                            //alert(taxes.length);
                            $("#taxes").html("");
                            for (var i = 0; i < taxes.length; i++) {
                                if (taxes[i].taxed === true) {
                                    $("#taxes").append('<li class="line-item tax-item"><strong>' + taxes[i].name + ":</strong> <span class=\"tax-amount\">" + taxes[i].taxamount + "</span><li>");
                                }
                            }
                        }
                    });
                    $.ajax({
                        url: vividStore.URLs.Cart + "/getTotal",
                        success: function (total) {
                            $(".total-amount").text(total);
                        }
                    });
                    if(addressType=='shipping') {
                        vividStore.showShippingMethods();
                    }
                } else {
                    $("#checkout-form-group-"+addressType+" .checkout-form-group-body").prepend('<div class="vivid-store-col-1 checkout-errors"><div class="alert alert-danger"></div></div>');
                    $("#checkout-form-group-"+addressType+" .alert").html(response.errors.join('<br>'));
                    $('.whiteout').remove();
                }
            },
            error: function (data) {
                alert("something went wrong");
                $(".whiteout").remove();

            }
        });
    }
    
};

$(function() {

    vividStore.updateBillingStates(true);
    vividStore.updateShippingStates(true);
    vividStore.showShippingMethods();
    vividStore.showPaymentForm();

    if ((window.location.origin + window.location.pathname) == vividStore.URLs.Checkout) {
        vividStore.loadViaHash();
    }

    $("#checkout-form-group-billing").submit(function (e) {
        e.preventDefault();
        vividStore.saveAddressInfo('billing');
    });
    $("#checkout-form-group-shipping").submit(function (e) {
        e.preventDefault();
        vividStore.saveAddressInfo('shipping');
    });
    $("#checkout-form-group-shipping-method").submit(function (e) {
        e.preventDefault();
        vividStore.waiting();
        var obj = $(this);
        if ($("#checkout-shipping-method-options input[type='radio']:checked").length < 1) {
            $('.whiteout').remove();
            alert("You must choose a shipping method");
        } else {
            var smID = $("#checkout-shipping-method-options input[type='radio']:checked").val();
            var methodText = $.trim($("#checkout-shipping-method-options input[type='radio']:checked").parent().text());
            obj.find('.summary-shipping-method').html(methodText);

            $.ajax({
                type: 'post',
                data: {smID: smID},
                url: vividStore.URLs.Cart + "/getShippingTotal",
                success: function (total) {
                    $("#shipping-total").text(total);
                    $.ajax({
                        url: vividStore.URLs.Cart + "/getTaxTotal",
                        success: function (results) {
                            var taxes = JSON.parse(results);
                            $("#taxes").html("");
                            for (var i = 0; i < taxes.length; i++) {
                                if (taxes[i].taxed === true) {
                                    $("#taxes").append('<li class="line-item tax-item"><strong>' + taxes[i].name + ":</strong> <span class=\"tax-amount\">" + taxes[i].taxamount + "</span></li>");
                                }
                            }
                        }
                    });
                    $.ajax({
                        url: vividStore.URLs.Cart + "/getTotal",
                        success: function (total) {
                            $(".total-amount").text(total);
                            vividStore.nextPane(obj);
                            $('.whiteout').remove();
                        }
                    });
                }
            });

        }
    });
    $(".btn-previous-pane").click(function () {
        //hide the body of the current pane, go to the next pane, show that body.
        var pane = $(this).closest(".checkout-form-group").find('.checkout-form-group-body').parent().prev();
        $('.active-form-group').removeClass('active-form-group');
        pane.addClass('active-form-group');

        $('html, body').animate({
            scrollTop: pane.parent().offset().top
        });

        $(this).closest(".checkout-form-group").prev().removeClass("checkout-form-group-complete");
    });
    $("#ckbx-copy-billing").change(function () {
        if ($(this).is(":checked")) {
            $("#checkout-shipping-first-name").val($("#checkout-billing-first-name").val());
            $("#checkout-shipping-last-name").val($("#checkout-billing-last-name").val());
            $("#checkout-shipping-company-name").val($("#checkout-billing-company-name").val());
            $("#checkout-shipping-email").val($("#checkout-billing-email").val());
            $("#checkout-shipping-phone").val($("#checkout-billing-phone").val());
            $("#checkout-shipping-address-1").val($("#checkout-billing-address-1").val());
            $("#checkout-shipping-address-2").val($("#checkout-billing-address-2").val());
            $("#checkout-shipping-country").val($("#checkout-billing-country").val());
            $("#checkout-shipping-city").val($("#checkout-billing-city").val());
            var billingstate = $("#checkout-billing-state").clone().val($("#checkout-billing-state").val()).attr("name", "checkout-shipping-state").attr("id", "checkout-shipping-state");
            $("#checkout-shipping-state").replaceWith(billingstate);
            $("#checkout-shipping-zip").val($("#checkout-billing-zip").val());
        }
    });
    $("#checkout-payment-method-options input[type='radio']").change(function () {
        vividStore.showPaymentForm();
    });
});