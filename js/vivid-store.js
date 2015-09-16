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
    vividStore.openModal("<div class='vivid-store-spinner'><i class='fa fa-spinner fa-spin'></i></div>");
},

exitModal: function(){
    $(".whiteout").remove();
},


//PRODUCT LIST
    
    //Open Product Modal
    productModal: function(pID){
        vividStore.waiting();
        $.ajax({
           url: PRODUCTMODAL,
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
            url: CARTURL+'/getmodal',
            success: function(data){
                vividStore.openModal(data);
            }
        });
    },


    //Add Item to Cart
    addToCart: function(pID, modal){
        if(modal==true){
            var form = $('#form-add-to-cart-modal-'+pID);
        } else {
            var form = $('#form-add-to-cart-'+pID);
        }
        var qty = $(form).find('.product-qty').val();
        if(qty > 0){
            var cereal = $(form).serialize(); //haha, cereal.
            vividStore.waiting();
            $.ajax({ 
                url: CARTURL+"/add",
                data: cereal,
                type: 'post',
                success: function(data) {
                    var res = jQuery.parseJSON(data);

                    if (res.product.pAutoCheckout == '1') {
                        window.location.href = CHECKOUTURL;
                        return false;
                    }

                    vividStore.displayCart(res);

                    $.ajax({
                       url: CARTURL+'/getTotalItems',
                       success: function(itemCount){
                           $(".vivid-store-utility-links .items-counter").text(itemCount);
                           if (itemCount > 0) {
                               $(".vivid-store-utility-links").removeClass('vivid-cart-empty');
                           }
                       } 
                    });
                    $.ajax({
                       url: CARTURL+'/getSubTotal',
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
            url: CARTURL+"/update",
            data: {instance: instanceID, pQty: qty},
            type: 'post',
            success: function(data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    vividStore.displayCart(res);
                }

                $.ajax({
                    url: CARTURL + '/getTotalItems',
                    success: function (itemCount) {
                        $(".vivid-store-utility-links .items-counter").text(itemCount);

                        if (itemCount == 0) {
                            $(".vivid-store-utility-links .items-counter").text(0);
                            $(".vivid-store-utility-links .total-cart-amount").text("");
                            $(".vivid-store-utility-links").addClass('vivid-cart-empty');
                        } else {
                            $.ajax({
                                url: CARTURL + "/getSubTotal",
                                success: function (total) {
                                    $(".cart-grand-total-value").text(total);
                                }
                            });

                            $.ajax({
                                url: CARTURL + '/getSubTotal',
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
            url: CARTURL+"/remove",
            data: {instance: instanceID},
            type: 'post',
            success: function(data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    vividStore.displayCart(res);
                }

                $.ajax({
                    url: CARTURL + '/getTotalItems',
                    success: function (itemCount) {
                        $(".vivid-store-utility-links .items-counter").text(itemCount);

                        if (itemCount == 0) {
                            $(".vivid-store-utility-links .items-counter").text(0);
                            $(".vivid-store-utility-links .total-cart-amount").text("");
                            $(".vivid-store-utility-links").addClass('vivid-cart-empty');
                        } else {
                            $.ajax({
                                url: CARTURL + "/getSubTotal",
                                success: function (total) {
                                    $(".cart-grand-total-value").text(total);
                                }
                            });

                            $.ajax({
                                url: CARTURL + '/getSubTotal',
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
             url: CARTURL+"/clear",
             success: function(data) {
                 if (modal) {
                     var res = jQuery.parseJSON(data);
                     vividStore.displayCart(res);
                 }

                 $.ajax({
                     url: CARTURL+"/getSubTotal",
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
            $(".checkout-form-group .checkout-form-group-body").hide();
            $("#checkout-form-group-"+hash+" .checkout-form-group-body").show();
        }
    },
    //loadViaHash();

    updateBillingStates: function(){
        var countryCode = $("#checkout-billing-country").val();
        var selectedState = $("#checkout-saved-billing-state").val();
        $.ajax({
           url: CHECKOUTURL+"/getstates",
           type: 'post',
           data: {country: countryCode, selectedState: selectedState, type: "billing"},
           success: function(states){
                $("#checkout-billing-state").replaceWith(states);
           } 
        });
    },
    
    
    
    updateShippingStates: function(){
        var countryCode = $("#checkout-shipping-country").val();
        var selectedState = $("#checkout-saved-shipping-state").val();
        $.ajax({
           url: CHECKOUTURL+"/getstates",
           type: 'post',
           data: {country: countryCode, selectedState: selectedState, type: "shipping"},
           success: function(states){
                $("#checkout-shipping-state").replaceWith(states);
           } 
        });
    },
    
    
    nextPane: function(obj){
       if($(obj)[0].checkValidity()){
           $(obj).closest(".checkout-form-group").find('.checkout-form-group-body').hide().parent().next().find(".checkout-form-group-body").show();
       } else { alert("not valid"); }
    },
    
    showShippingMethods: function(){
        $.ajax({
            url: CHECKOUTURL+"/getShippingMethods",
            success: function(html){
                $("#checkout-shipping-method-options").html(html);
            }
        });  
    },
    
    showPaymentForm: function(){
        var pmID = $("#checkout-payment-method-options input[type='radio']:checked").attr('data-payment-method-id');
        $('.payment-method-container').addClass('hidden');
        $(".payment-method-container[data-payment-method-id='"+pmID+"']").removeClass('hidden');
    }
    
    
};

vividStore.updateBillingStates();
vividStore.updateShippingStates();
vividStore.showShippingMethods();
vividStore.showPaymentForm();

$("#checkout-form-group-billing").submit(function(e){
        e.preventDefault();
        var email = $("#email").val();
        var bfName = $("#checkout-billing-first-name").val();
        var blName = $("#checkout-billing-last-name").val();
        var bPhone = $("#checkout-billing-phone").val();
        var bAddress1 = $("#checkout-billing-address-1").val();
        var bAddress2 = $("#checkout-billing-address-2").val();
        var bCountry = $("#checkout-billing-country").val();
        var bCity = $("#checkout-billing-city").val();
        var bState = $("#checkout-billing-state").val();
        var bPostal = $("#checkout-billing-zip").val();
        vividStore.waiting();
        var obj = $(this);
        $.ajax({
            url: CHECKOUTURL+"/updater",
            type: 'post',
            data: {adrType: 'billing', email: email, fName: bfName, lName: blName, phone: bPhone, addr1: bAddress1, addr2: bAddress2, count: bCountry, city: bCity, state: bState, postal: bPostal},
            //dataType: 'json',
            success: function(result){
                //var test = null;
                var $errors = JSON.parse(result);
                if($errors.error == false){
                    $(".whiteout").remove();
                    vividStore.nextPane(obj);   
                    //update tax
                    $.ajax({
                        url: CARTURL+"/getTaxTotal",
                        success: function(results){
                            var taxes = JSON.parse(results);
                            //alert(taxes.length);
                            $("#taxes").html("");
                            for(var i=0;i<taxes.length;i++){
                                if(taxes[i].taxed===true){
                                    $("#taxes").append('<li class="line-item tax-item"><strong>'+taxes[i].name+":</strong> <span class=\"tax-amount\">"+taxes[i].taxamount+"</span><li>");
                                }
                            }
                        } 
                    });
                    $.ajax({
                        url: CARTURL+"/getTotal",
                        success: function(total){
                            $(".total-amount").text(total);
                        }
                    });
                } else {
                    $("#checkout-form-group-billing .checkout-form-group-body").prepend('<div class="vivid-store-col-1"><div class="alert alert-danger"></div></div>');
                    $("#checkout-form-group-billing .alert").html($errors.errors.join('<br>'));
                    $('.whiteout').remove();
                }
            },
            error: function(data){
                alert("something went wrong");
                $(".whiteout").remove();
                
            }  
       });
       
    });
    $("#checkout-form-group-shipping").submit(function(e){
       e.preventDefault();
       var sfName = $("#checkout-shipping-first-name").val();
       var slName = $("#checkout-shipping-last-name").val();
       var sAddress1 = $("#checkout-shipping-address-1").val();
       var sAddress2 = $("#checkout-shipping-address-2").val();
       var sCountry = $("#checkout-shipping-country").val();
       var sCity = $("#checkout-shipping-city").val();
       var sState = $("#checkout-shipping-state").val();
       var sPostal = $("#checkout-shipping-zip").val();
       vividStore.waiting();
       var obj = $(this);
       $.ajax({
           url: CHECKOUTURL+"/updater",
           type: 'post',
           data: {adrType: 'shipping', fName: sfName, lName: slName, addr1: sAddress1, addr2: sAddress2, count: sCountry, city: sCity, state: sState, postal: sPostal},
           //dataType: 'json', 
           success: function(result){
                var $errors = JSON.parse(result);
                if($errors.error == false){
                    $(".whiteout").remove();
                    vividStore.nextPane(obj);   
                    //update tax
                    $.ajax({
                        url: CARTURL+"/getTaxTotal",
                        success: function(results){
                            var taxes = JSON.parse(results);
                            $("#taxes").html("");  
                            for(var i=0;i<taxes.length;i++){
                                if(taxes[i].taxed===true){
                                    $("#taxes").append('<li class="line-item tax-item"><strong>'+taxes[i].name+":</strong> <span class=\"tax-amount\">"+taxes[i].taxamount+"</span></li>");
                                }
                            }
                        } 
                    });
                    vividStore.showShippingMethods();
                    $.ajax({
                        url: CARTURL+"/getTotal",
                        success: function(total){
                            $(".total-amount").text(total);
                        }
                    });
                } else {
                    $("#checkout-form-group-shipping .checkout-form-group-body").prepend('<div class="vivid-store-col-1"><div class="alert alert-danger"></div></div>');
                    $("#checkout-form-group-shipping .alert").html($errors.errors.join('<br>'));
                    $('.whiteout').remove();
                }
            },
            error: function(data){
                alert("something went wrong");
                $(".whiteout").remove();
                
            } 
       });
       
    });
    $("#checkout-form-group-shipping-method").submit(function(e){
        e.preventDefault();
        vividStore.waiting();
        var obj = $(this);
        if($("#checkout-shipping-method-options input[type='radio']:checked").length < 1){
            $('.whiteout').remove();
            alert("You must choose a shipping method");            
        } else {
            var smID = $("#checkout-shipping-method-options input[type='radio']:checked").val();
            $.ajax({
                type: 'post',
                data: {smID: smID },
                url: CARTURL+"/getShippingTotal",
                success: function(total){
                    $("#shipping-total").text(total);
                    vividStore.nextPane(obj);  
                    $('.whiteout').remove();                    
                }
            });
            $.ajax({
                url: CARTURL+"/getTaxTotal",
                success: function(results){
                    var taxes = JSON.parse(results);
                    $("#taxes").html("");  
                    for(var i=0;i<taxes.length;i++){
                        if(taxes[i].taxed===true){
                            $("#taxes").append("<strong>"+taxes[i].name+":</strong> <span class=\"tax-amount\">"+taxes[i].taxamount+"</span><br>");
                        }
                    }
                } 
            });
            $.ajax({
                url: CARTURL+"/getTotal",
                success: function(total){
                    $(".total-amount").text(total);
                }
            });
        }
    });
    $(".btn-previous-pane").click(function(){
       //hide the body of the current pane, go to the next pane, show that body.
       $(this).closest(".checkout-form-group").find('.checkout-form-group-body').hide().parent().prev().find(".checkout-form-group-body").show();        
    });
    $("#ckbx-copy-billing").change(function(){
       if($(this).is(":checked")){
           $("#checkout-shipping-first-name").val($("#checkout-billing-first-name").val());
           $("#checkout-shipping-last-name").val($("#checkout-billing-last-name").val());
           $("#checkout-shipping-email").val($("#checkout-billing-email").val());
           $("#checkout-shipping-phone").val($("#checkout-billing-phone").val());
           $("#checkout-shipping-address-1").val($("#checkout-billing-address-1").val());
           $("#checkout-shipping-address-2").val($("#checkout-billing-address-2").val());
           $("#checkout-shipping-country").val($("#checkout-billing-country").val());
           $("#checkout-shipping-city").val($("#checkout-billing-city").val());
           var billingstate = $("#checkout-billing-state").clone().val($("#checkout-billing-state").val()).attr("name","checkout-shipping-state").attr("id","checkout-shipping-state");
           $("#checkout-shipping-state").replaceWith(billingstate);
           $("#checkout-shipping-zip").val($("#checkout-billing-zip").val());
       } 
    });
    $("#checkout-payment-method-options input[type='radio']").change(function(){
        vividStore.showPaymentForm();
    });
