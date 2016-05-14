<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<div class="rule-type-form" data-complete-function="onNewCartQuantityMinimumRuleType">
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
            <?php echo $form->label('qtyMin', t("Cart Quantity Minimum")); ?>
            <?php echo $form->text('qtyMin'); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function onNewCartQuantityMinimumRuleType(){
        var qty = $('.vivid-store-dialog #qtyMin').val();

        var params = {
            qty: qty
        }
        var listItemTemplate = _.template($('#subtotal-minimum-list-item-template').html());
        return listItemTemplate(params);
    }
</script>

<script type="text/x-template" id="cart-qty-minimum-list-item-template">
    <?=t("If there's at least %s items in the cart", '<strong><%=qty%></strong>')?>
</script>
