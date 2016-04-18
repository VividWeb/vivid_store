<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<style type="text/css">
    #product-search { position: relative; }
    #product-search-results { position: absolute; z-index: 2; display: none; top: 57px; padding: 10px 20px;background: #fff; width: 100%; height: 90px; overflow-y: scroll; border: 1px solid #ccc; box-shadow: 0 0 10px #ccc; }
    #product-search-results.active { display: block; }
    #product-search-results ul { padding: 0; }
    #product-search-results ul li { list-style: none; padding: 2px 5px; cursor: pointer; }
    #product-search-results ul li:hover { background: #0088ff; color: #fff; }
</style>
<div class="rule-type-form" data-complete-function="onNewProductExistsRuleType">
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?=$form->label('productExistsSearch','Search for a product')?>
                <?=$form->text('productExistsSearch',null,array('onkeyup'=>'searchForProduct()'))?>
                <?=$form->hidden('pID',$pID)?>
                <div id="product-search-results">
                    <ul id="results-list">

                    </ul>
                </div>
                <div class="alert alert-info">
                    <strong><?=t("Selected Product:")?></strong>
                    <span id="selected-product"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function onNewProductExistsRuleType(){
        var subtotalMinimum = $('.vivid-store-dialog #subtotalMinimum').val();

        var params = {
            subtotal: subtotalMinimum
        }
        var listItemTemplate = _.template($('#subtotal-minimum-list-item-template').html());
        return listItemTemplate(params);
    }
    function searchForProductExists(){

        // Set Search String
        var searchString =  $(".vivid-store-dialog input#productSearch").val();

        // Do Search
        if(searchString.length > 0){
            $(".vivid-store-dialog #product-search-results").addClass("active");
            $.ajax({
                type: "post",
                url: "<?=URL::to('/productfinder')?>",
                data: {query: searchString},
                success: function(html){
                    $(".vivid-store-dialog ul#results-list").html(html);
                    $(".vivid-store-dialog #product-search-results ul li").click(function(){
                        var pID = $(this).attr('data-product-id');
                        var productName = $(this).text();
                        $(".vivid-store-dialog #pID").val(pID);
                        $(".vivid-store-dialog #product-search-results").removeClass("active");
                        $('.vivid-store-dialog #productSearch').val('');
                        $(".vivid-store-dialog #selected-product").html(productName);
                    });
                    $(".vivid-store-dialog *:not(#product-search-results ul li)").click(function(){
                        $(".vivid-store-dialog #product-search-results").removeClass("active");
                    })
                }
            });

        }
        else{
            $(".vivid-store-dialog #product-search-results").removeClass("active");
        }
</script>

<script type="text/x-template" id="subtotal-minimum-list-item-template">
    If the product: <strong><%=item%></strong>, is in the shopping cart
</script>