<?php extract($vars); ?>

<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?php echo $form->label('baseRate',t("Base Price")); ?>
            <?php echo $form->text('baseRate'); ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?php echo $form->label('perItemRate',t("Additional Price per Item")); ?>
            <?php echo $form->text('perItemRate'); ?>
        </div>
    </div>
</div>  
<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?php echo $form->label('countries',t("Which Countries does this Apply to?")); ?>
            <?php echo $form->select('countries',array('all'=>t("All Countries"),'selected'=>t("Certain Countries"))); ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?php echo $form->label('countriesSelected',t("If Certain Countries, which?")); ?>
            <?php echo $form->select('countriesSelected',$countryList,array('multiple'=>'multiple')); ?>
        </div>
    </div>
</div> 
<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?php echo $form->label('minimumAmount',t("Minimum Purchase Amount for this rate to apply")); ?>
            <?php echo $form->text('minimumAmount','0'); ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?php echo $form->label('maximumAmount',t("Maximum Purchase Amount for this rate to apply")); ?>
            <?php echo $form->text('maximumAmount','0'); ?>
            <p class="help-block"><?=t("Leave at 0 for no maximum")?></p>
        </div>
    </div>
</div> 