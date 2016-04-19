$(function(){
    
    var url = window.location.pathname.toString();
    $('#group-filters li a').each(function(){
        var href = $(this).attr('href');
        if(url == href) {
           $(this).parent().addClass("active");
        }
    });
            
    $("a[data-pane-toggle]").click(function(e){
        e.preventDefault();
        var paneTarget = $(this).attr('href');
        paneTarget = paneTarget.replace('#','');     
        $(".store-pane, a[data-pane-toggle]").removeClass('active');
        $('#'+paneTarget).addClass("active"); 
        $(this).addClass("active");
    });
    $(".btn-delete-group").click(function(){
        var groupID = $(this).parent().attr("data-group-id");
        var confirmDelete = confirm('Are You Sure?');
        if(confirmDelete == true) {
            var deleteurl = $(".group-list").attr("data-delete-url");
            $.ajax({ 
                url: deleteurl+"/"+groupID,
                success: function() {
                    $("li[data-group-id='"+groupID+"']").remove();
                },
                error: function(){
                    alert("Something went wrong");
                }
            });             
        }
    });
    $(".btn-save-group-name").click(function(){
        var groupID = $(this).parent().attr("data-group-id");
        var saveurl = $(".group-list").attr("data-save-url");
        var gName = $(this).parent().find(".edit-group-name").val();
        $.ajax({ 
            url: saveurl+"/"+groupID,
            data: {gName: gName},
            type: 'post',
            success: function() {
                $("li[data-group-id='"+groupID+"']").find(".group-name").text(gName);
                $("li[data-group-id='"+groupID+"']").find(".btn-edit-group-name,.group-name").show();
                $("li[data-group-id='"+groupID+"']").find(".edit-group-name, .btn-cancel-edit, .btn-save-group-name").attr("style","display: none");
            },
            error: function(){
                alert("something went wrong");
            }
        });    
    });
    $(".btn-edit-group-name").click(function(){
        $(this).parent().find(".btn-edit-group-name,.group-name").hide();
        $(this).parent().find(".edit-group-name, .btn-cancel-edit, .btn-save-group-name").attr("style","display: inline-block !important"); 
    });
    $(".btn-cancel-edit").click(function(){
       $(this).parent().find(".btn-edit-group-name,.group-name").show();
       $(this).parent().find(".edit-group-name, .btn-cancel-edit, .btn-save-group-name").attr("style","display: none");  
    });
    
    $("#btn-delete-order").click(function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var confirmDelete = confirm('Are You Sure?');
        if(confirmDelete == true) {
            window.location = url;
        }
    });
    $("#btn-generate-page").click(function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var pageTemplate = $("#selectPageTemplate").val();
        var confirmDelete = confirm('Just to let you know, any changes to the product will not be saved. Are you sure you want to proceed?');
        if(confirmDelete == true) {
            window.location = url+'/'+pageTemplate;        
        }
    });

    $(".add-to-panel-list .panel-heading").click(function(){
       $(this).next().toggleClass('open');
    });
    $('.add-to-panel-list li a').click(function(e){
        e.preventDefault();
        var type = $(this).attr('data-promotion');
        var handle = $(this).attr('data-handle');
        if(type=='reward-type'){
            var title = VividStoreStrings.addRewardType;
        } else if(type=='rule-type'){
            var title = VividStoreStrings.addRuleType;
        }
        $('#'+handle+'-'+type+'-form').dialog({
            title: title,
            width: 500,
            height: 400,
            modal: true,
            open: function(){
                $('#'+handle+'-'+type+'-form').closest('.ui-dialog').addClass('vivid-store-dialog ccm-ui');
            },
            buttons: [
                {
                    text: VividStoreStrings.add,
                    click: function () {
                        var listItemTemplate = _.template($('#promotion-list-item').html());
                        var completeFunction = $('#'+handle+'-'+type+'-form').find('.'+type+'-form').attr('data-complete-function')
                        var content = window[completeFunction]();
                        var params = {
                            handle: handle,
                            content: content
                        }
                        type = type.replace('-type','');
                        $("#promotion-"+type+"-list").append(listItemTemplate(params));
                        $(this).dialog('close');
                        $(".add-to-panel-list .panel-body").removeClass('open');
                    }
                },
                {
                    text: VividStoreStrings.cancel,
                    click: function () {
                        $(this).dialog('close');
                    }
                }
            ]
        });
    });

});


function searchForProduct(id){
    var target = '#product-search-form-'+id;
    var inputField = $(target + " .product-search-input");
    var searchString = $(this).val();
    if(searchString.length > 0){
        $(target + " .product-search-results").addClass("active");
        $.ajax({
            type: "post",
            url: $(target).attr('data-ajax-url'),
            data: {query: searchString},
            success: function(html){
                $(target + " .results-list").html(html);
                $(target + " .product-search-results ul li").click(function(){
                    var pID = $(this).attr('data-product-id');
                    var productName = $(this).text();
                    $(target + " .product-id-field").val(pID);
                    $(target + " .product-search-results").removeClass("active");
                    $(target + " .product-search-input").val('');
                    $(target + " .selected-product").html(productName);
                });
                $("*:not(.product-search-results ul li)").click(function(){
                    $(target " .product-search-results").removeClass("active");
                })
            }
        });
    } else {
        $(target + " .product-search-results").removeClass("active");
    }
}

function updateTaxStates(){
    var countryCode = $("#taxCountry").val();
    var selectedState = $("#savedTaxState").val();
    var stateutility = $("#settings-tax").attr("data-states-utility");
    $.ajax({
       url: stateutility,
       type: 'post',
       data: {country: countryCode, selectedState: selectedState, type: "tax"},
       success: function(states){
           $("#taxState").replaceWith(states);

           if (states.indexOf(" selected ") >= 0) {
               $("#taxState").prepend("<option value=''></option>");
           } else {
               $("#taxState").prepend("<option value='' selected='selected'></option>");
           }
       } 
    });
}
updateTaxStates();
