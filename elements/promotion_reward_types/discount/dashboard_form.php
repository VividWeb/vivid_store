<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<div class="reward-type-form" data-complete-function="onNewDiscountRewardType">
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
            <?php echo $form->label('discountCalculation', t("Discount Type")); ?>
            <?php echo $form->select('discountCalculation', array('percentage'=>t("Percentage off"),'flatRate'=>t("Flat rate"))); ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
            <?php echo $form->label('discountAmount', t("Discount Amount")); ?>
            <?php echo $form->text('discountAmount'); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12" id="discount-subject-selector">
            <div class="form-group">
                <?php echo $form->label('discountSubject', t("Discount applies to:")); ?>
                <?php echo $form->select('discountSubject', array(
                    'grandTotal'=>t("Grand Total"),
                    'subTotal'=>t("Sub Total"),
                    'productGroup'=>t("Products within a Group")
                ),$discountSubject,array('onChange'=>'onDiscountSubjectChange()')); ?>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 hidden" id="product-selector">
            <div class="form-group">
                <?php echo $form->label('discountTarget', t("Group to discount")); ?>
                <select name="discountTarget" id="discountTarget" class="form-control">
                    <?php foreach ($grouplist as $group) { ?>
                        <option value="<?=$group->getGroupID()?>"><?=$group->getGroupName()?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function onDiscountSubjectChange(){
        if($('.vivid-store-dialog #discountSubject option:selected').val() == 'productGroup'){
            $('.vivid-store-dialog #discount-subject-selector').removeClass('col-sm-12').addClass('col-sm-6');
            $('.vivid-store-dialog #product-selector').removeClass('hidden');
        } else {
            $('.vivid-store-dialog #discount-subject-selector').removeClass('col-sm-6').addClass('col-sm-12');
            $('.vivid-store-dialog #product-selector').addClass('hidden');
        }
    }
    function onNewDiscountRewardType(){
        var discountCalculation = $('.vivid-store-dialog #discountCalculation').val();
        var discountAmount = $('.vivid-store-dialog #discountAmount').val();
        var discountSubject = $('.vivid-store-dialog #discountSubject').val();
        var discountTarget = $('.vivid-store-dialog #discountTarget').val();
        if(discountCalculation=='percentage'){
            var discountString = discountAmount + "%";
        } else {
            var discountString = "$" + discountAmount;
        }
        if(discountSubject == 'productGroup'){
            var discountTargetString = $('.vivid-store-dialog #discountTarget option:selected').text() + " Product Group";
        } else {
            var discountTargetString = $('.vivid-store-dialog #discountSubject option:selected').text();
        }
        var params = {
            discountAmount: discountString,
            discountTarget: discountTargetString
        }
        var listItemTemplate = _.template($('#discount-list-item-template').html());
        return listItemTemplate(params);
    }
</script>

<script type="text/x-template" id="discount-list-item-template">
    <?=t('%s off of %s','<strong><%=discountAmount%></strong>', '<strong><%=discountTarget%></strong>')?>
</script>
