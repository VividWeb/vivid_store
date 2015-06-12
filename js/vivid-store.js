var vividStore = {


waiting: function(){
    $("body").append("<div class='whiteout'><div class='vivid-store-spinner'><i class='fa fa-spinner fa-spin'></i></div></div>");
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
               $(".whiteout").remove();
               vividStore.craftProductModal(modalContent);
           },
           error: function(){
               alert("something went wrong");
           }
        });
    },
     
    craftProductModal: function(content){
       $("body").append("<div class='whiteout'>"+content+"</div>"); 
    },
    



//SHOPPING CART

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
                success: function() {
                    $(".whiteout").remove();
                    $.ajax({
                       url: CARTURL+'/getTotalItems',
                       success: function(itemCount){
                           $(".vivid-store-utility-links .items-counter").text(itemCount);
                       } 
                    });
                }
            });
        } else {
            alert(QTYMESSAGE);
        }
    },
    
    //Update Item in Cart
    updateItem: function(instanceID){
        var qty = $("*[data-instance-id='"+instanceID+"']").find(".cart-list-product-qty input").val();
        vividStore.waiting();
        $.ajax({ 
            url: CARTURL+"/update",
            data: {instance: instanceID, pQty: qty},
            type: 'post',
            success: function() {                
                $.ajax({
                    url: CARTURL+"/getSubTotal",
                    success: function(total){
                        $(".cart-grand-total-value").text(total);
                        $(".whiteout").remove();
                    }
                });
                $.ajax({
                   url: CARTURL+'/getTotalItems',
                   success: function(itemCount){
                       $(".vivid-store-utility-links .items-counter").text(itemCount);
                   } 
                });
            }
        }); 
    },
    
    //Remove Item in Cart
    removeItem: function(instanceID){
        vividStore.waiting();
        $.ajax({ 
            url: CARTURL+"/remove",
            data: {instance: instanceID},
            type: 'post',
            success: function() {
                $.ajax({
                    url: CARTURL+"/getSubTotal",
                    success: function(total){
                        $(".cart-grand-total-value").text(total);
                        $(".whiteout").remove();
                        $("*[data-instance-id='"+instanceID+"']").remove();
                    }
                });
                 $.ajax({
                   url: CARTURL+'/getTotalItems',
                   success: function(itemCount){
                       $(".vivid-store-utility-links .items-counter").text(itemCount);
                   } 
                });
            }
        }); 
    },
    
    //Clear the Cart
    clearCart: function(){
         $.ajax({ 
             url: CARTURL+"/clear",
             success: function() {
                 $.ajax({
                    url: CARTURL+"/getSubTotal",
                    success: function(total){
                        $(".cart-grand-total-value").text(total);
                        $(".cart-page-cart-list-item").remove();
                        $(".whiteout").remove();
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
    
    
    showPaymentForm: function(){
        var pmID = $("#checkout-payment-method-options input[type='radio']:checked").attr('data-payment-method-id');
        $('.payment-method-container').addClass('hidden');
        $(".payment-method-container[data-payment-method-id='"+pmID+"']").removeClass('hidden');
    }
    
    
};

vividStore.updateBillingStates();
vividStore.updateShippingStates();
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
                            $("#taxes").html("");  
                            for(var i=0;i<taxes.length;i++){
                                $("#taxes").append("<strong>"+taxes[i].name+":</strong> <span class=\"tax-amount\">"+taxes[i].taxamount+"</span><br>");
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
                    alert($errors.errors.join('\n'));
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
                                $("#taxes").append("<strong>"+taxes[i].name+":</strong> <span class=\"tax-amount\">"+taxes[i].taxamount+"</span><br>");
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
                    alert($errors.errors.join('\n'));
                    $('.whiteout').remove();
                }
            },
       });
       
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