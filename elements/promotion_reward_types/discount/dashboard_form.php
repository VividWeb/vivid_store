<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>
<script type="text/x-template" id="discount-reward-type-form" data-complete-function="addNewDiscountReward">
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <label class="form-label" for="discountCalculation"><?=t("Discount Type")?></label>
                <select name="discountCalculation" id="discountCalculation" class="form-control">
                    <option value="percentage"><?=t("Percentage off")?></option>
                    <option value="flatRate"><?=t("Flat rate")?></option>
                </select>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <label class="form-label" for="discountAmount"><?=t("Discount Amount")?></label>
                <input type="text" name="discountAmount" id="discountAmount" class="form-control">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12" id="discount-subject-selector">
            <div class="form-group">
                <label class="form-label" for="discountSubject"><?=t("Discount applies to")?></label>
                <select name="discountSubject" id="discountSubject" class="form-control" onChange="onDiscountSubjectChange()">
                    <option value="grandTotal"><?=t("Grand Total")?></option>
                    <option value="subTotal"><?=t("Sub Total")?></option>
                    <option value="productGroup"><?=t("Products within a Group")?></option>
                </select>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 hidden" id="product-selector">
            <div class="form-group">
                <label class="form-label" for="discountTarget"><?=t("Group to discount")?></label>
                <select name="discountTarget" id="discountTarget" class="form-control">
                    <?php foreach ($grouplist as $group) {
    ?>
                        <option value="<?=$group->getGroupID()?>"><?=$group->getGroupName()?></option>
                    <?php 
} ?>
                </select>
            </div>
        </div>
    </div>
</script>
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
    function addNewDiscountReward(){
        var fields = {
            rewardTypeID: <?=$rewardType->getID()?>,
            discountCalculation: $('.vivid-store-dialog #discountCalculation').val(),
            discountAmount: $('.vivid-store-dialog #discountAmount').val(),
            discountSubject: $('.vivid-store-dialog #discountSubject').val(),
            discountTarget: $('.vivid-store-dialog #discountTarget').val()
        }
        $.ajax({
            url: '<?=URL::to('/dashboard/store/promotions/utility/save_reward/')?>',
            data: {
                rewardTypeID: fields.rewardTypeID,
                discountCalculation: fields.discountCalculation,
                discountAmount: fields.discountAmount,
                discountSubject: fields.discountSubject,
                discountTarget: fields.discountTarget
            },
            method: "post",
            error: function(){
                alert('something went wrong');
            },
            success: function(response){
                onNewDiscountRewardSuccess(fields,JSON.parse(response));
            }
        });
    }
    function onNewDiscountRewardSuccess(fields,response){
        if(fields.discountCalculation=='percentage'){
            var discountString = fields.discountAmount + "%";
        } else {
            var discountString = "$" + fields.discountAmount;
        }
        if(fields.discountSubject == 'productGroup'){
            var discountTargetString = $('.vivid-store-dialog #discountTarget option:selected').text() + " Product Group";
        } else {
            var discountTargetString = $('.vivid-store-dialog #discountSubject option:selected').text();
        }

        var params = {
            discountAmount: discountString,
            discountTarget: discountTargetString,
            rewardTypeID: fields.rewardTypeID,
            rewardTypeRewardID: response.rewardTypeRewardID
        }

        var listItemTemplate = _.template($('#discount-list-item-template').html());
        listItemTemplate = listItemTemplate(params);
        $(window).trigger('on_promotion_reward_save',[{type:'reward',handle:'<?=$rewardType->getHandle()?>',template:listItemTemplate}]);
    }
</script>

<script type="text/x-template" id="discount-list-item-template">
    <input type="hidden" name="rewardTypeID[]" value="<%=rewardTypeID%>" />
    <input type="hidden" name="rewardTypeRewardID[]" value="<%=rewardTypeRewardID%>" />
    <?=t('%s off of %s', '<strong><%=discountAmount%></strong>', '<strong><%=discountTarget%></strong>')?>
</script>
