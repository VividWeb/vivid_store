<?php defined('C5_EXECUTE') or die(_("Access Denied."));
extract($vars);
?>
<div class="clearfix col-container">
    <div class="vivid-store-col-2">
        <div class="form-group">
            <label for="checkout-credit-card"><?=t("Credit Card Number")?></label>
            <?php echo $form->text('authnet-checkout-credit-card'); ?>
        </div>
    </div>
</div>
<div class="clearfix col-container">
    <div class="vivid-store-col-2">
        <div class="col-container">
            <div class="form-group vivid-store-col-3">
                <label for="checkout-exp-month"><?=t("Month")?></label>
                <?php echo $form->select('authnet-checkout-exp-month', array(
                    "01"=>"01 ".t("Jan"),
                    "02"=>"02 ".t("Feb"),
                    "03"=>"03 ".t("Mar"),
                    "04"=>"04 ".t("Apr"),
                    "05"=>"05 ".t("May"),
                    "06"=>"06 ".t("Jun"),
                    "07"=>"07 ".t("Jul"),
                    "08"=>"08 ".t("Aug"),
                    "09"=>"09 ".t("Sep"),
                    "10"=>"10 ".t("Oct"),
                    "11"=>"11 ".t("Nov"),
                    "12"=>"12 ".t("Dec")
                 )); ?>
            </div>
            <div class="form-group vivid-store-col-3">
                <label for="checkout-exp-year"><?=t("Year")?></label>
                <?php echo $form->select('authnet-checkout-exp-year', $years); ?>
            </div>
            <div class="form-group vivid-store-col-3">
                <label for="beanstream-checkout-cvv"><?=t("CCV")?></label>
                <?php echo $form->text('authnet-checkout-ccv'); ?>
            </div>
        </div>
    </div>    
</div>
