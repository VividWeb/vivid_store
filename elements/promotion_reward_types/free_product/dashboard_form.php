<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<div class="reward-type-form" data-complete-function="onNewFreeProductRewardType">
    <div class="row">
        <div class="col-xs-12 col-sm-11">
            <div class="form-group">
                <?=$productFinder?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function onNewFreeProductRewardType(){
        var productName = $('#free_product-reward-type-form .selected-product').text();

        var params = {
            productName: productName
        }
        var listItemTemplate = _.template($('#free-product-list-item-template').html());
        $('#free_product-reward-type-form .selected-product').text('');
        return listItemTemplate(params);
    }
</script>

<script type="text/x-template" id="free-product-list-item-template">
    <?=t('Free Product: %s','<strong><%=productName%></strong>')?>
</script>
