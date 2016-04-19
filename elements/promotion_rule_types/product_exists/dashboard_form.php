<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<div class="rule-type-form" data-complete-function="onNewProductExistsRuleType">
    <div class="row">
        <div class="col-xs-12 col-sm-11">
            <div class="form-group">
                <?=$productFinder?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function onNewProductExistsRuleType(){
        var productID = $('#product_exists-rule-type-form .product-id-field').val();
        var productName =  $('.vivid-store-dialog .selected-product').text();

        var params = {
            productID: productID,
            productName: productName
        }
        var listItemTemplate = _.template($('#product-exists-list-item-template').html());
        $('#product_exists-rule-type-form .selected-product').text('');
        return listItemTemplate(params);
    }
</script>

<script type="text/x-template" id="product-exists-list-item-template">
    <?=t(' If the product: %s, is in the shopping cart', '<strong><%=productName%></strong>')?>
</script>
