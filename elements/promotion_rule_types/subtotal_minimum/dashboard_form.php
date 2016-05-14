<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<div class="rule-type-form" data-complete-function="onNewSubtotalMinimumRuleType">
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
            <?php echo $form->label('subtotalMinimum', t("Subtotal Minimum")); ?>
            <?php echo $form->text('subtotalMinimum'); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function onNewSubtotalMinimumRuleType(){
        var subtotalMinimum = $('.vivid-store-dialog #subtotalMinimum').val();

        var params = {
            subtotal: subtotalMinimum
        }
        var listItemTemplate = _.template($('#subtotal-minimum-list-item-template').html());
        return listItemTemplate(params);
    }
</script>

<script type="text/x-template" id="subtotal-minimum-list-item-template">
    <?=t("If the Subtotal is at least %s", '<strong><%=subtotal%></strong>')?>
</script>
